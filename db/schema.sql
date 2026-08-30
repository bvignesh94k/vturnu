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
