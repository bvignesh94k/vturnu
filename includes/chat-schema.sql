-- Vee, the AI chat assistant (includes/chat.php, added 24 Nov 2026). The site
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
