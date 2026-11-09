-- Book a Meeting (added 9 Nov 2026, includes/booking.php). One row per meeting
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
