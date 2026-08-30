# AI_INSTRUCTIONS.md

## PROJECT

Name: LeadForge AI

Owner: APARK IT Solutions

Purpose:
Internal AI-powered project discovery and sales intelligence platform.

Tagline:
"Find the Right Business. Discover the Right Project."

---

## PRIMARY GOAL

User should normally provide only:

LOCATION

Example:
Indore

System automatically:

Location
→ Business Discovery
→ Website Discovery
→ Website Analysis
→ Business Classification
→ Digital Maturity
→ Missing Capabilities
→ Software Need Prediction
→ Opportunity Score
→ Project Value Estimate
→ AI Personalized Email
→ User Approval
→ Email
→ Reply Tracking
→ AI Follow-up
→ CRM
→ Meeting
→ Proposal
→ Won/Lost

---

## PRODUCT PRINCIPLE

This is NOT:

- Generic CRM
- Generic AI chatbot
- Generic lead scraper
- Wappalyzer clone
- BuiltWith clone
- Bulk spam tool

This IS:

AI-powered software sales opportunity detection for APARK IT Solutions.

Primary question:

"What software/service could APARK realistically sell to this business, and why?"

---

## BRAND

Product:
LeadForge AI

Company:
APARK IT Solutions

Tagline:
Find the Right Business. Discover the Right Project.

UI:
Modern
Professional
Premium
Clean
B2B
Responsive
Mobile-first

---

## TECH STACK

Backend:
Laravel 12
PHP 8.3+
MySQL 8+
Redis
Laravel Queue
Laravel Scheduler

Frontend:
Blade
Bootstrap 5
Alpine.js where useful
Chart.js where useful

AI/Crawler:
Python 3.12+
FastAPI
Playwright
BeautifulSoup
httpx
lxml

AI:
OpenAI-compatible API
OpenRouter-compatible
Optional Ollama

---

## ARCHITECTURE

Laravel:
- Authentication
- CRM
- Campaigns
- Leads
- Services
- Emails
- Follow-ups
- Dashboard
- Reports
- Settings
- API

Python:
- Website crawling
- Page extraction
- Technology detection
- Website signals
- AI worker operations

Redis:
- Queue
- Cache

MySQL:
- Persistent data

---

## CORE RULE

Use deterministic code BEFORE AI.

Use code/rules for:

- HTTPS
- status code
- page count
- response time
- meta tags
- sitemap
- robots.txt
- forms
- WhatsApp links
- login detection
- technology signals
- duplicate detection

Use AI for:

- business understanding
- classification
- ambiguous interpretation
- opportunity reasoning
- service recommendation
- pitch generation

Never send complete HTML to AI.

Send compact structured JSON.

---

## TOKEN OPTIMIZATION

CRITICAL.

Do not waste AI tokens.

Before AI:

1. Crawl
2. Extract
3. Clean
4. Deduplicate
5. Summarize
6. Extract signals
7. Limit text
8. Create compact JSON
9. Send only relevant information

Cache analysis by content hash.

If website has not materially changed:
DO NOT call AI again.

Store:

- model
- prompt version
- input tokens
- output tokens
- cost
- duration

Avoid repeating identical AI calls.

---

## AI OUTPUT

AI must return strict JSON.

Required structure:

{
  "business": {},
  "signals": [],
  "opportunities": [],
  "recommended_pitch_angle": "",
  "summary": ""
}

Validate JSON before database storage.

Never trust raw AI output.

---

## EVIDENCE RULE

Every recommendation must distinguish:

Evidence
Inference
Recommendation
Confidence

Never present inference as fact.

Bad:
"Company uses Excel for orders."

Good:
"No online order workflow was detected on the public website; manual processing may therefore be possible."

---

## OPPORTUNITY SCORING

0-100.

Weights:

Business Fit: 20
Pain/Gap: 20
Missing Capability: 20
Company Potential: 15
Technology Gap: 10
Contact Availability: 5
Project Potential: 5
AI Confidence: 5

Classification:

90-100 HOT
75-89 HIGH
60-74 MEDIUM
40-59 LOW
0-39 IGNORE

Score must be explainable.

---

## APARK SERVICES

Initial services:

Website Development
Website Redesign
E-commerce
CRM
ERP
Inventory Management
Dealer Management
Customer Portal
Transport Management
Fleet Management
Billing
Mobile App
API Integration
WhatsApp Automation
AI Automation
Business Process Automation
Maintenance
SEO/Performance
Custom Software

Services are configurable.

Do not hardcode service logic in multiple places.

---

## PROJECT VALUE

Estimates are configurable sales estimates.

Initial examples:

Website:
₹50K–₹1.5L

E-commerce:
₹1L–₹4L

CRM:
₹1L–₹5L

ERP:
₹3L–₹15L+

Mobile App:
₹2L–₹8L+

Dealer Portal:
₹1.5L–₹5L

Automation:
₹50K–₹3L

API:
₹25K–₹2L

Never present AI estimates as guaranteed quotations.

---

## LEAD PIPELINE

NEW
DISCOVERED
ANALYZED
QUALIFIED
CONTACTED
REPLIED
INTERESTED
MEETING
PROPOSAL
NEGOTIATION
WON
LOST
NOT_INTERESTED
DO_NOT_CONTACT

---

## EMAIL RULES

AI-generated emails must be:

- personalized
- concise
- professional
- human
- relevant
- evidence-based

Never invent:

- revenue
- employee count
- software
- internal process
- customers
- partnerships
- problems
- personal information

Default:

AI Generate
→ User Review
→ User Approve
→ Send

Do NOT enable unrestricted bulk auto-send.

Follow-ups must stop when:

- reply
- meeting
- interested
- not interested
- do-not-contact
- bounce
- campaign disabled

---

## FOLLOW-UP

Default suggestion:

Day 0
Initial email

Day 3
Follow-up 1

Day 7
Follow-up 2

Day 14
Final follow-up

Day 30
Optional re-engagement

These are configurable.

---

## DISCOVERY / CRAWLING

Only use public/compliant data sources.

Do NOT implement:

- CAPTCHA bypass
- bot detection bypass
- fingerprint spoofing
- stealth scraping
- proxy rotation for evasion
- unauthorized authenticated scraping
- security challenge bypass

Respect:

- source terms
- rate limits
- robots.txt where applicable
- privacy requirements
- anti-spam requirements

Crawler must have:

- timeout
- retry
- rate limit
- max response size
- max pages
- SSRF protection

---

## SSRF SECURITY

Crawler must reject:

- localhost
- 127.0.0.0/8
- private IP ranges
- internal hostnames
- cloud metadata endpoints
- arbitrary internal ports

Validate DNS-resolved IP before requesting.

---

## DATABASE PRINCIPLES

Use:

- foreign keys
- indexes
- unique constraints
- timestamps
- soft deletes where appropriate

Avoid unnecessary tables.

Core entities:

users
services
service_rules
service_case_studies
campaigns
leads
lead_contacts
lead_opportunities
website_scans
website_pages
website_technologies
website_signals
ai_analyses
ai_recommendations
prompt_templates
prompt_versions
ai_usage_logs
email_accounts
email_campaigns
email_messages
follow_ups
activities
tasks
notifications
audit_logs
settings

---

## CODE QUALITY

Before writing code:

1. Inspect existing code.
2. Identify reusable components.
3. Identify dependencies.
4. Check migrations.
5. Check routes.
6. Check existing services.
7. Check existing UI patterns.

Do not blindly rewrite.

Avoid:

- duplicate classes
- duplicate APIs
- dead code
- unnecessary abstractions
- fake data
- placeholder production functionality
- hardcoded secrets

Use:

- service classes
- repositories only where justified
- DTO/resources where useful
- validation
- policies
- jobs
- events where useful
- clean separation of concerns

---

## PERFORMANCE

Always consider:

- pagination
- indexes
- eager loading
- caching
- queue jobs
- AI caching
- database query count
- N+1 prevention

Heavy work must be queued.

---

## QUEUE JOBS

Use jobs for:

- discovery
- crawling
- analysis
- AI
- email generation
- email sending
- reply sync
- follow-ups
- reports

Statuses:

PENDING
PROCESSING
COMPLETED
FAILED
RETRYING

A single failed website must never fail the entire campaign.

---

## UI

Primary UX:

Location
→ Find Projects

Keep UI simple.

Use:

- clear cards
- compact filters
- responsive tables
- mobile cards
- skeleton loaders
- progress indicators
- empty states
- confirmation dialogs
- toast notifications

No unnecessary animations.

No horizontal scrolling on mobile.

---

## MOBILE

All major functions must work on mobile:

- Dashboard
- Campaigns
- Leads
- Lead detail
- Opportunity analysis
- Email preview
- Follow-ups
- Pipeline
- Notifications

---

## DASHBOARD PRIORITY

Show:

HOT LEADS
HIGH POTENTIAL
FOLLOW-UPS TODAY
EMAIL REPLIES
MEETINGS
PIPELINE VALUE

Then:

TOP OPPORTUNITIES

Sort primarily by:

Opportunity Score
then
Estimated Project Value
then
Confidence

---

## AI FEEDBACK LOOP

Store:

AI prediction
→ User action
→ Prospect response
→ Won/Lost
→ Reason

Example rejection reasons:

Existing software
No budget
Not interested
Wrong contact
Wrong industry
Timing
Already outsourced
Other

This data may later improve scoring.

Do not implement machine-learning retraining until sufficient clean data exists.

---

## SECURITY

Always implement:

- authentication
- authorization
- CSRF
- validation
- rate limiting
- secure OAuth
- encrypted sensitive settings
- audit logs
- SSRF protection
- file validation

Never log:

- passwords
- OAuth secrets
- API keys
- access tokens

---

## DEVELOPMENT WORKFLOW

Follow this order:

1. Inspect repository
2. Architecture
3. Database
4. Backend
5. UI
6. Python worker
7. Crawler
8. AI
9. Discovery
10. CRM
11. Email
12. Follow-up
13. Reports
14. Tests
15. Security
16. Performance
17. Documentation

Do not skip tests.

---

## WHEN A TASK IS GIVEN

Before implementing:

1. Read this file.
2. Inspect relevant existing code.
3. Identify affected files.
4. Make the smallest correct change.
5. Test.
6. Fix errors.
7. Report exactly what changed.

Do not modify unrelated modules.

---

## RESPONSE FORMAT FOR DEVELOPMENT TASKS

Keep responses concise.

Report:

1. What was implemented
2. Files changed
3. Database changes
4. Tests run
5. Result
6. Any blocker

Do not repeat project requirements.

Do not paste entire files unless explicitly requested.

---

## DEFINITION OF DONE

A feature is NOT complete until:

- functionality works
- validation exists
- error handling exists
- responsive UI works
- tests pass
- no obvious console errors
- no obvious N+1 queries
- no secrets committed
- documentation updated where necessary

---

## FINAL PRODUCT PRINCIPLE

Optimize for:

LEAD QUALITY
RELEVANCE
EXPLAINABILITY
PERSONALIZATION
CONVERSION

Not:

SCRAPING VOLUME
SPAM VOLUME
AI TOKEN VOLUME

The purpose of LeadForge AI is to help APARK IT Solutions find real software-development opportunities and convert them into projects.