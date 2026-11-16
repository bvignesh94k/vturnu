# Email Delivery Is Now an IT Problem: What Gmail's Sender Rules Mean for CIOs

**By B. Vignesh, Founder & CEO, VTurnU**

When marketing emails stop reaching customers, the first meeting is usually about subject lines and send times. The real cause is often somewhere else: in DNS records, in sending services nobody remembers approving, and in a domain reputation that every department shares. That is IT territory.

Since 2024, Gmail has set firm rules for anyone who sends email to its users, and it has been enforcing them more strictly. For a CIO, this is no longer a marketing detail. It is part of keeping the company's communications working.

## What Gmail now requires

Google's [email sender guidelines](https://support.google.com/mail/answer/81126) and its FAQ set out the rules:

- Every sender should authenticate mail with SPF or DKIM.
- Bulk senders, which Google defines as domains sending close to 5,000 or more messages a day to personal Gmail accounts, must use SPF, DKIM and DMARC. Once a domain crosses that line, Google treats it as a bulk sender permanently.
- Marketing and subscribed messages from bulk senders must support one-click unsubscribe, and requests should be honoured within two days.
- Spam complaints should stay below 0.1% and must never reach 0.3%.

Google says that from November 2025 it has been ramping up enforcement, and that non-compliant traffic can face temporary and permanent rejections.

## Why it lands on the CIO's desk

In most companies, email is sent in the company's name by far more systems than IT knows about. A typical list includes the main mail platform, the CRM, a marketing automation tool, the helpdesk, the recruitment system, invoicing software, website contact forms and a sales team's outreach tool. Each one sends as your domain. Each one needs to be authenticated. And each one shares the same reputation: a careless campaign from one tool can hurt delivery of invoices and support replies from another.

DNS is usually controlled by IT, so only IT can fix this properly.

## A 30-day plan for IT teams

**Week 1: find every sender.** Ask each department which tools send email on its behalf, and check the vendor list for anything with a "send as" feature. Write it all down: the tool, the owner, the volume and the address it sends from.

**Week 2: authenticate each one.** Make sure every service is covered by SPF and signs mail with DKIM for your domain. Remove old services from SPF records when they are no longer used.

**Week 3: start DMARC in monitoring mode.** A DMARC record with a policy of "none" changes nothing about delivery, but it starts sending reports that show every system using your domain. Most teams find at least one sender they did not know about.

**Week 4: separate the streams.** Move bulk marketing email to a subdomain, so that a poorly received campaign cannot damage the domain your staff and customers rely on. Check that marketing messages carry one-click unsubscribe.

**After that: tighten slowly.** Once the reports show that legitimate mail passes, move the DMARC policy towards quarantine and then reject, in stages. Watch Google Postmaster Tools for spam rates and authentication results along the way.

## Make it part of governance

Two small changes keep the problem from returning:

- **Add email authentication to vendor onboarding.** No new tool may send as the company domain until IT has set up SPF and DKIM for it.
- **Give deliverability an owner and a number.** Spam complaint rate and DMARC pass rate belong on a monthly IT dashboard, next to uptime and security alerts.

Email is easy to take for granted because it usually works. The rules have changed, and the cost of ignoring them is no longer a weaker campaign. It is order confirmations, invoices and password resets that never arrive. That is a problem worth solving before a customer notices it.

---

*B. Vignesh is the Founder & CEO of VTurnU, a B2B digital marketing and web development company based in Chennai, India. He has worked in digital marketing since 2017.*

Website: [vturnu.com](https://vturnu.com) · LinkedIn: [linkedin.com/in/vigneshuday](https://www.linkedin.com/in/vigneshuday/)

===META===
Title: Email Delivery Is Now an IT Problem: What Gmail's Sender Rules Mean for CIOs
Meta title: Gmail's Sender Rules: Why Email Delivery Is Now an IT Problem
Meta description: SPF, DKIM, DMARC, one-click unsubscribe and spam limits: what Gmail requires of senders, and a 30-day plan for IT teams.
Keywords: email authentication, DMARC, Gmail sender requirements, IT governance, email deliverability
Image: digitalterminal-email-delivery-it-problem.jpg (1200 x 675)
Image alt text: Banner reading "Email delivery is now an IT problem", listing four steps: find every sender, authenticate each one, start DMARC in monitoring, separate marketing mail.
Source named in the text: Gmail Help, Email sender guidelines and FAQ.
Exclusive: written for Digital Terminal and not published anywhere else.
