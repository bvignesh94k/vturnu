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
