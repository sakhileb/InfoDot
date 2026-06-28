# Dot.Pulse — Community Intelligence Platform

**Role:** The Human Layer of the Dot Ecosystem  
**URL:** `pulse.infodot.app`  
**Tagline:** The operating system of business communities.

---

## Vision

Dot.Pulse is not a Facebook clone. It is the **Operating System of Business Communities** — combining the best of LinkedIn, Reddit, Product Hunt, Discord, Twitter, Apple Community, and GitHub Discussions into one platform natively integrated with the Dot ecosystem.

What is missing from the current Dot ecosystem is the **human layer** — a place where users share experiences, discover updates, provide feedback, learn from each other, and build trust in the ecosystem. Dot.Pulse fills that gap.

Every conversation on Dot.Pulse feeds intelligence back into Dot.Analytics. Community knowledge becomes organisational knowledge.

---

## Why "Dot.Pulse"

Every company should know the **pulse** of its users and community. The name is active, modern, and connotes continuous monitoring of community health.

> "See what's happening on Dot.Pulse."

Rejected alternatives: Dot.Hear (passive), Dot.Community (generic), Dot.Forum (legacy connotation).

---

## Platform Architecture

```
Dot Ecosystem

├── Dot.Analytics     ← receives community intelligence from Pulse
│
├── Dot.Pulse         ← the community layer
│   ├── Home Feed
│   ├── Communities
│   ├── Company Pages
│   ├── Personal Profiles
│   ├── Marketplace
│   ├── Live Events
│   └── Messaging
│
└── Shared: PostgreSQL · Redis · Reverb · S3 · Meilisearch · Auth
```

---

## User Types

| Role | Description |
|------|-------------|
| Guest | Read-only access to public content |
| Customer | Standard user, personal account |
| Business | Company account with pages and branding |
| Enterprise | Private communities, internal feeds, SSO |
| Developer | API access, agent/integration publishing |
| Partner | Verified Dot ecosystem partner |
| Employee | Internal company feed access |
| Moderator | Community moderation tools |
| Administrator | Full platform management |

---

## Home Feed

Think TikTok meets LinkedIn. AI-ranked, not chronological.

**Content types in feed:**
- Updates, videos, articles, screenshots
- AI workflow showcases
- Business announcements, achievements
- Hiring posts, event invites
- New integrations, releases, tutorials
- Polls, case studies, questions

**AI ranking signals:**
- Industry, business type, role
- Interests, projects, current platform in use
- Organisation, previous searches, communities joined

---

## Post Types

Every post carries a structured type for filtering and intelligence:

```
Announcement    Question        Idea            Bug Report
Release         Success Story   Showcase        Tutorial
Agent           Integration     Discussion      Event
Article         Poll            Video           Job
Marketplace
```

---

## AI-Generated Post Enrichment

Every post is automatically enriched with:

| Field | Description |
|-------|-------------|
| Summary | AI-generated 2-sentence summary |
| Tags | Auto-extracted topic tags |
| Sentiment | Positive / Neutral / Negative / Mixed |
| Category | Mapped to post type taxonomy |
| Language | Detected and stored for translation |
| Topics | Semantic topic clusters |
| Keywords | Extracted key terms |
| Duplicate Score | Similarity to existing posts (0–1) |
| Spam Score | Probability of spam (0–1) |
| Safety Score | Policy compliance (0–1) |
| Business Relevance | Relevance to business context (0–1) |
| Community Score | Predicted engagement potential |

---

## AI Moderation Pipeline

Every post passes through automated moderation before publishing:

```
Toxicity Check → Hate Speech Detection → Spam Filter
→ Scam Detection → Threat Analysis → NSFW Filter
→ Duplicate Detection → Fake Review Detection → Bot Detection
→ APPROVE / FLAG / REJECT
```

Flagged posts route to human moderators with AI rationale attached.

---

## Company Pages

Businesses receive full profile pages:

```
Logo + Banner          Follower count        Employee directory
Products & platforms   Reviews               Posts & videos
AI Agents published    Integrations          Website + Support
Location + Industries  Verified badge
```

---

## Personal Profiles

```
Avatar + Cover banner   Bio + Skills          Achievements + Badges
Followers + Following   Communities           Posts + Projects
Verified badge          AI Score              Contribution Score
Expertise tags          Mentor / Creator / Builder level
```

---

## Communities

Reddit-style community spaces, user-creatable:

**Default communities:**
```
Mining          Agriculture     AI & Agents     Manufacturing
Automation      Developers      Startups        ERP
CRM             Fleet           Construction    Logistics
Healthcare      Finance         Agriculture     Education
```

Each community has: moderators, rules, pinned posts, weekly digest, trending topics, expert roster.

---

## Reputation System

Stack Overflow–inspired trust model:

| Level | Criteria |
|-------|---------|
| Community Points | Earned per post, answer, reaction |
| Expertise | Domain-specific verified knowledge |
| Solutions Accepted | Answers marked as solution by OP |
| Helpful Answers | Upvoted responses |
| Verified Knowledge | Admin-verified expert in domain |
| Mentor Level | Consistent high-quality guidance |
| Creator Level | Published agents / templates |
| Builder Level | Published integrations / extensions |

---

## Business Reviews

Verified reviews for:
- AI Agents
- Integrations
- Companies
- Support quality
- Platform features
- Marketplace items

AI fraud detection identifies: fake reviews, coordinated campaigns, suspicious patterns, incentivised reviews.

---

## Live Events

Companies can host community events:

```
Webinars        Product launches     Q&A sessions
AMAs            Training sessions    Product demos
Town halls      Community meetups
```

RSVP, reminders, recordings, post-event discussion threads.

---

## Marketplace

Publish and install with one click:

```
AI Agents           Templates           Dashboard layouts
Prompt libraries    Automations         Integrations
Extensions          Workflow blueprints
```

Every marketplace item: rating, reviews, installs count, version history, changelog.

---

## News & Announcements

Official Dot ecosystem communications:
```
Release notes       Version updates     Security notices
Roadmap updates     Community highlights   Featured businesses
Partner announcements
```

---

## Gamification

```
Daily streak            Badges              Achievement milestones
Leaderboards            Community rank      Verified Expert badge
AI Creator badge        Automation Builder  Top Contributor
```

---

## AI Features (Differentiator)

Every user has a personal AI assistant contextualised to their organisation:

**Example queries:**
> "Summarise today's fleet discussions."  
> "Find the best automation for invoice processing."  
> "Who solved a similar driver shortage issue?"  
> "Recommend experts in agricultural telematics."  
> "Show companies using Bell Equipment in the ecosystem."

The AI searches across every post, reply, and resolved discussion.

---

## AI Knowledge Graph

Every discussion is transformed into structured knowledge:

```
Knowledge Nodes     Relationships       Topic clusters
Expert profiles     Company knowledge   Solutions library
Problem taxonomy    Root cause library  Fix database
```

Over time Dot.Pulse becomes the institutional memory of the Dot ecosystem — every solved problem, every shared success, every vetted recommendation.

---

## Messaging

- Private 1:1 chat
- Group conversations
- Business-to-customer chat
- Voice notes
- Screen sharing
- File sharing
- AI assistant embedded in every conversation

---

## Enterprise Features

For Enterprise-tier organisations:

```
Private internal communities    Internal announcements
Organisation knowledge base     Employee feed (internal-only)
Organisation directory          AI-powered internal search
Internal events                 Custom branding
```

---

## AI Agents Running Behind the Scenes

| Agent | Responsibility |
|-------|---------------|
| Community Moderator Agent | Spam, abuse, duplicates, policy violations |
| Trend Analyst Agent | Trending topics, industries, emerging discussions |
| Customer Success Agent | Unanswered questions, routing to right teams |
| Release Communications Agent | Technical notes → user-friendly announcements |
| Knowledge Curator Agent | Solved discussions → searchable documentation |
| Reputation Agent | Trust score updates from helpful contributions |
| Business Intelligence Agent | Sentiment analysis around products and features |
| Translation Agent | Real-time multilingual conversations |
| Recommendation Agent | Communities, experts, products, connections |
| Fraud Detection Agent | Fake accounts, coordinated campaigns, suspicious reviews |

---

## Intelligence Feed to Dot.Analytics

Every interaction on Dot.Pulse contributes structured intelligence to Dot.Analytics:

| Signal | Analytics Use |
|--------|--------------|
| Post sentiment | Brand health tracking |
| Feature requests | Product roadmap prioritisation |
| Bug report clusters | Engineering triage |
| Community growth | Platform adoption metrics |
| Marketplace installs | Agent/integration ROI |
| Expert engagement | Partner programme signals |
| Review trends | Product quality scoring |

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Frontend | Livewire 3 + Volt + Alpine.js |
| Component library | Flux UI + TailwindCSS |
| Realtime | Laravel Reverb + Echo |
| Queue | Laravel Horizon |
| Search | Laravel Scout + Meilisearch |
| AI | Claude / OpenAI via Dot.Agents |
| Storage | AWS S3 (media, avatars, attachments) |
| Database | PostgreSQL 16 (shared ecosystem DB) |

---

## Key Database Tables

```
users                   profiles                organizations
posts                   post_enrichments        comments
reactions               polls                   poll_votes
communities             community_memberships   community_rules
notifications           media                   hashtags
mentions                followers               messages
conversations           events                  event_rsvps
badges                  achievements            user_badges
reviews                 marketplace_items       marketplace_installs
reports                 moderation_logs         moderation_decisions
knowledge_nodes         knowledge_edges         knowledge_sources
search_index
```

---

## Build Priority

**Phase 1:** Core feed · Post types · Profiles · Communities · Basic AI enrichment  
**Phase 2:** Company pages · Reputation system · Marketplace MVP · Moderation pipeline  
**Phase 3:** Live events · Messaging · Enterprise features · Knowledge Graph  
**Phase 4:** AI agents (all 10) · Full Dot.Analytics integration · Advanced gamification  
**Phase 5:** Mobile apps · Partner API · SDK for third-party integrations
