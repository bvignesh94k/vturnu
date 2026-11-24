-- VTurnU: Postgres schema for the Vercel migration.
-- Replaces every file-based storage/*.json + enquiries.jsonl mechanism.
-- Run once against the provisioned database before first deploy:
--   psql "$POSTGRES_URL" -f db/schema.sql

create table if not exists enquiries (
    id           bigserial primary key,
    created_at   timestamptz not null default now(),
    source       text not null default '',
    name         text not null default '',
    email        text not null default '',
    mobile       text not null default '',
    company      text not null default '',
    designation  text not null default '',
    service      text not null default '',
    budget       text not null default '',
    message      text not null default '',
    -- CRM fields, filled in from the admin panel after capture.
    status       text not null default 'new',
    notes        jsonb not null default '[]',
    value        text not null default '',
    priority     text not null default 'normal',
    owner        text not null default '',
    followup     date,
    tags         text not null default '',
    activity     jsonb not null default '[]',
    -- Soft delete: Postgres already has its own durability, so there is no
    -- need for the old file-based backup-before-delete dance.
    deleted_at   timestamptz
);
create index if not exists enquiries_active_idx on enquiries (created_at desc) where deleted_at is null;

create table if not exists admin_users (
    id             bigserial primary key,
    username       text not null unique,
    password_hash  text not null,
    updated_at     timestamptz not null default now()
);

-- Replaces storage/blog-custom.json, cases-custom.json, resources-custom.json.
create table if not exists content_overrides (
    content_type  text not null,
    slug          text not null,
    data          jsonb not null,
    updated_at    timestamptz not null default now(),
    primary key (content_type, slug)
);

-- Replaces storage/rate-forms.json, storage/audit-rate.json and
-- storage/admin-lockout.json. The shape of `hits` differs by bucket: a
-- sliding-window array of unix timestamps for forms/audit, or a single
-- {count,last} object for admin-lockout, matching each limiter's original
-- file-based semantics exactly.
create table if not exists rate_limits (
    bucket      text not null,
    key_hash    text not null,
    hits        jsonb not null default '[]',
    updated_at  timestamptz not null default now(),
    primary key (bucket, key_hash)
);

-- CRM (added 3 Nov 2026). Safe to re-run: every statement checks first.
-- Where each lead came from (includes/crm.php crm_attribution()), its grade as
-- columns rather than a line in the message, and when it was first answered.
alter table enquiries add column if not exists channel       text not null default '';
alter table enquiries add column if not exists channel_group text not null default '';
alter table enquiries add column if not exists attribution   jsonb not null default '{}';
alter table enquiries add column if not exists form_page     text not null default '';
alter table enquiries add column if not exists country       text not null default '';
alter table enquiries add column if not exists grade         text not null default '';
alter table enquiries add column if not exists score         smallint;
alter table enquiries add column if not exists contacted_at  timestamptz;

-- Proposals and follow-ups sent from the admin, with what the client did with them.
create table if not exists proposals (
    id              bigserial primary key,
    enquiry_id      bigint not null references enquiries(id),
    parent_id       bigint references proposals(id),
    token           text not null unique,
    kind            text not null default 'proposal' check (kind in ('proposal', 'followup')),
    subject         text not null,
    body            text not null,
    to_email        text not null,
    sent_by         text not null default '',
    resend_id       text not null default '',
    sent_at         timestamptz not null default now(),
    opened_at       timestamptz,
    last_opened_at  timestamptz,
    open_count      integer not null default 0,
    viewed_at       timestamptz,
    last_viewed_at  timestamptz,
    view_count      integer not null default 0,
    read_seconds    integer not null default 0,
    accepted_at     timestamptz,
    accept_note     text not null default ''
);
create index if not exists proposals_enquiry_idx on proposals (enquiry_id, sent_at desc);

-- CRM settings (sender, automations). Kept out of content_overrides, which is
-- published as /content-overrides.json for the public pages.
create table if not exists crm_settings (
    key         text primary key,
    value       jsonb not null,
    updated_at  timestamptz not null default now()
);

-- Leads from before tracking: grade and form page from the summary block the
-- message already carries, and an honest "Not tracked" for the channel.
update enquiries set grade = substring(message from 'Lead grade: ([ABC])'),
       score = substring(message from 'Lead grade: [ABC] \(([0-9]+)/10\)')::smallint
 where grade = '' and message ~ 'Lead grade: [ABC] \([0-9]+/10\)';
update enquiries set form_page = substring(message from 'Source page: (/[^\n]*)')
 where form_page = '' and message ~ 'Source page: /';
update enquiries set channel = 'Not tracked', channel_group = 'Not tracked' where channel = '';

-- Book a Meeting (added 9 Nov 2026, includes/booking.php; the site runs the same
-- statements from includes/booking-schema.sql on first use). One row per meeting
-- thread: a personal link sent from the CRM starts as 'requested'; a call booked
-- on the connect@vturnu.com Google Calendar is 'booked' (moving it keeps the
-- row and counts a reschedule); 'cancelled' keeps the link open to book again.
create table if not exists meetings (
    id               bigserial primary key,
    enquiry_id       bigint references enquiries(id),
    token            text not null unique,
    status           text not null default 'requested' check (status in ('requested', 'booked', 'cancelled')),
    name             text not null default '',
    email            text not null default '',
    phone            text not null default '',
    company          text not null default '',
    notes            text not null default '',
    time_zone        text not null default '',
    start_at         timestamptz,
    end_at           timestamptz,
    event_id         text not null default '',
    ical_uid         text not null default '',
    event_link       text not null default '',
    meet_url         text not null default '',
    sequence         integer not null default 0,
    rsvp             text not null default '',
    reschedules      integer not null default 0,
    reminder_ids     jsonb not null default '[]',
    request_subject  text not null default '',
    request_body     text not null default '',
    requested_by     text not null default '',
    requested_at     timestamptz,
    request_mail_id  text not null default '',
    opened_at        timestamptz,
    viewed_at        timestamptz,
    booked_at        timestamptz,
    cancelled_at     timestamptz,
    cancelled_by     text not null default '',
    cancel_reason    text not null default '',
    created_at       timestamptz not null default now(),
    updated_at       timestamptz not null default now(),
    check (status <> 'booked' or (start_at is not null and end_at > start_at))
);
create index if not exists meetings_enquiry_idx on meetings (enquiry_id, created_at desc);
create index if not exists meetings_start_idx on meetings (start_at) where status = 'booked';
-- Two booked calls can never overlap, even when two people press Book in the same second.
do $$ begin
    alter table meetings add constraint meetings_no_overlap
        exclude using gist (tstzrange(start_at, end_at, '[)') with &&) where (status = 'booked');
exception when duplicate_object or duplicate_table then null;
end $$;

-- Vee, the AI chat assistant (added 24 Nov 2026, includes/chat.php; the site
-- runs the same statements from includes/chat-schema.sql on first use).
-- runs these statements itself on first use (chat_ready()), and the same block
-- sits at the end of db/schema.sql. Safe to re-run: every statement checks first.

-- One row per conversation. The contact details and lead fields are what the
-- visitor typed (email and phone found by pattern, the rest read by the AI);
-- the mailed_* columns make every notification email go out exactly once.
create table if not exists chat_sessions (
    id              bigserial primary key,
    token           text not null unique,
    created_at      timestamptz not null default now(),
    updated_at      timestamptz not null default now(),
    last_visitor_at timestamptz,
    ip_hash         text not null default '',
    country         text not null default '',
    time_zone       text not null default '',
    first_page      text not null default '',
    last_page       text not null default '',
    attribution     text not null default '',
    name            text not null default '',
    email           text not null default '',
    phone           text not null default '',
    company         text not null default '',
    website         text not null default '',
    service         text not null default '',
    budget          text not null default '',
    budget_label    text not null default '',
    timeline        text not null default '',
    intent          text not null default '',
    summary         text not null default '',
    handoff         boolean not null default false,
    visitor_msgs    integer not null default 0,
    ai_replies      integer not null default 0,
    spam_hits       integer not null default 0,
    status          text not null default 'open',
    enquiry_id      bigint,
    lead_mailed_at  timestamptz,
    mailed_msgs     integer not null default 0,
    mailed_msg_id   bigint not null default 0,
    mailed_at       timestamptz
);
create index if not exists chat_sessions_updated_idx on chat_sessions (updated_at desc);
create index if not exists chat_sessions_enquiry_idx on chat_sessions (enquiry_id);

-- Every message in a conversation: role is visitor, vee or team (phase 2).
create table if not exists chat_messages (
    id          bigserial primary key,
    session_id  bigint not null references chat_sessions (id) on delete cascade,
    role        text not null,
    body        text not null,
    meta        jsonb not null default '{}'::jsonb,
    created_at  timestamptz not null default now()
);
create index if not exists chat_messages_session_idx on chat_messages (session_id, id);

-- Free AI quota used per model per day (UTC), and how long a model rests after
-- it answers "too many requests".
create table if not exists chat_usage (
    day         date not null,
    model       text not null,
    requests    integer not null default 0,
    units       integer not null default 0,
    failures    integer not null default 0,
    cool_until  timestamptz,
    primary key (day, model)
);
