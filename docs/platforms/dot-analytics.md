# Dot.Analytics — Enterprise Intelligence Platform

**Role:** The Intelligence Layer of the Dot Ecosystem  
**URL:** `analytics.infodot.app`  
**Tagline:** Not a BI tool. The central nervous system of the Dot ecosystem.

---

## Vision

Dot.Analytics is not another dashboard. It is the **Enterprise Intelligence Platform (EIP)** that continuously consumes data from every Dot platform, understands relationships across them, and feeds intelligence back into all of them.

Every platform contributes knowledge. Every platform receives intelligence.

Traditional tools (Power BI, Tableau, Looker, Zoho Analytics) visualise data users prepare for them. Dot.Analytics understands the relationships across the entire Dot ecosystem natively, predicts outcomes, recommends actions, and feeds insight back into every micro-platform automatically.

The result: a living intelligence network where every Dot platform both **contributes to** and **benefits from** Dot.Analytics. As organisations adopt more Dot products the intelligence becomes richer — a network effect competitors cannot replicate with external connectors.

---

## Architecture

```
                      Dot.Analytics
                    (Intelligence Core)

       /         |         |         |         \
      /          |         |         |          \
Dot.Fleet    Dot.CRM   Dot.HR   Dot.Pulse   Dot.Documents
Dot.API      Dot.Agents Dot.Vault Dot.Payments Dot.Support
Dot.Finance  Dot.Flow   Dot.Hear  Dot.Security Dot.Inventory
Dot.Assets   Dot.Auction Dot.Agri                    ...
                  + Third-Party Systems
```

---

## Data Sources & Intelligence Produced

### Dot.Fleet
**Contributes:** GPS, trips, fuel, maintenance, driver behaviour, machine utilisation, idle time, engine hours, route history, equipment health  
**Analytics produces:** Fleet health score · Cost per km · Cost per ton · Fleet utilisation · Predictive maintenance · Driver efficiency · Route optimisation · Fuel theft detection

### Dot.CRM
**Contributes:** Leads, opportunities, sales, customer activity, quotes, deals, campaigns  
**Analytics produces:** Customer lifetime value · Win probability · Sales forecasts · Churn predictions · Pipeline health · Lead quality · Customer sentiment

### Dot.HR
**Contributes:** Attendance, leave, payroll, training, performance, certifications, skills  
**Analytics produces:** Workforce efficiency · Burnout prediction · Promotion recommendations · Skill gap analysis · Department performance · Retention risks

### Dot.Documents
**Contributes:** Contracts, reports, policies, SOPs, invoices, PDFs  
**Analytics produces:** Expiring contract alerts · Missing signature detection · Compliance risks · AI document summaries · Document relationship mapping

### Dot.Pulse
**Contributes:** Comments, reviews, discussions, likes, polls, suggestions, community trends, feature requests  
**Analytics produces:** Sentiment analysis · Trending topics · Brand perception · Feature demand signals · Community health · Customer satisfaction · Competitor mentions · Emerging problem detection

### Dot.Support
**Contributes:** Tickets, chats, calls, resolution times, categories, satisfaction scores  
**Analytics produces:** Support demand prediction · Agent workload forecasting · Customer frustration scoring · Recurring issue detection · Knowledge gap identification

### Dot.Inventory
**Contributes:** Stock, warehouses, suppliers, purchases, sales  
**Analytics produces:** Stock shortage forecasting · Dead stock identification · Supplier risk scoring · Inventory turnover · Demand forecasting

### Dot.Payments
**Contributes:** Revenue, expenses, transactions, refunds, invoices, subscriptions  
**Analytics produces:** Cash flow analysis · Fraud detection · Profitability scoring · Revenue forecasting · Customer value segmentation

### Dot.Security
**Contributes:** Logins, threats, audit logs, permissions, suspicious behaviour  
**Analytics produces:** Risk scoring · Threat prediction · Compliance reports · Security posture tracking

### Dot.API
**Contributes:** API usage, latency, errors, requests, consumers  
**Analytics produces:** API health monitoring · Traffic prediction · Bottleneck detection · Abuse detection

### Dot.Flow (Automation)
**Contributes:** Workflow data, executions, failures, approvals  
**Analytics produces:** Workflow optimisation · Failure prediction · Automation ROI

### Dot.Assets
**Contributes:** Equipment, buildings, maintenance records, ownership  
**Analytics produces:** Asset depreciation · Replacement prediction · Utilisation analysis · Lifecycle analytics

### Dot.Agents
**Contributes:** Agent usage, prompt history, automation success, AI confidence, execution history, decision logs, learning patterns  
**Analytics produces:** Most valuable agents · ROI per agent · Failed automation tracking · AI adoption metrics · Department usage · Prompt quality scoring

---

## Cross-Platform Intelligence — The Real Superpower

Single-platform tools cannot do this. Dot.Analytics can.

**Mining Company Example:**  
Dot.Fleet + Dot.HR + Dot.Payments + Dot.Support + Dot.Documents combined produce:

> "Productivity dropped 8% because three certified operators were on leave. This caused increased overtime costs, delayed maintenance, a spike in support tickets, and reduced production efficiency."

No single platform knows this. Dot.Analytics does.

**Customer Risk Example:**  
Customer complains on Dot.Pulse → Analytics correlates:
- Sentiment decreased
- Sales to that customer dropped
- Support tickets increased
- Invoices became overdue
- AI agents contacted them twice
- Contract expires next month

Result: **High Risk Customer** — one insight, six platforms.

---

## Intelligence Engines

Instead of isolated modules, Dot.Analytics is powered by specialised engines:

| Engine | Purpose |
|--------|---------|
| Data Intelligence Engine | Ingestion, normalisation, lineage |
| Business Intelligence Engine | KPIs, dashboards, reports |
| Operational Intelligence Engine | Fleet, assets, maintenance |
| Financial Intelligence Engine | Cash flow, fraud, forecasting |
| People Intelligence Engine | HR, workforce, retention |
| Document Intelligence Engine | Contracts, compliance, expiry |
| Community Intelligence Engine | Pulse/Hear sentiment, trends |
| AI Intelligence Engine | Agent ROI, adoption, quality |
| Predictive Intelligence Engine | Forecasting, risk scoring |
| Decision Intelligence Engine | Recommendations, next-best-action |
| Risk Intelligence Engine | Multi-domain risk aggregation |
| Security Intelligence Engine | Threats, compliance, posture |
| Customer Intelligence Engine | CLV, churn, satisfaction |
| Asset Intelligence Engine | Depreciation, replacement |
| Mining Intelligence Engine | Production, safety, compliance |
| Agriculture Intelligence Engine | Yield, irrigation, equipment |
| Construction Intelligence Engine | Progress, labour, materials |

---

## Universal Intelligence Graph

Dot.Analytics maintains a living knowledge graph connecting entities across all platforms:

```
Customer → Orders → Invoices → Payments → Support Tickets
       → Equipment → Operators → Maintenance → Fuel
       → Community Posts → AI Conversations
       → Contracts → Projects → Inventory → Suppliers
```

This graph enables AI to answer questions no traditional BI system can:

> "Why is Customer X becoming unprofitable?"

The system traces relationships across every platform to construct the answer.

---

## Business DNA Model

Every organisation on the Dot ecosystem develops a unique **Business DNA Model** — an evolving operational fingerprint built from:

- How the company operates
- Seasonal trends
- Decision patterns
- Team performance
- Customer behaviour
- Financial cycles
- Operational bottlenecks
- Risk tolerance
- Growth opportunities

This model becomes increasingly accurate over time. AI recommendations become tailored to that specific organisation rather than relying on generic analytics benchmarks.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Frontend | Livewire 3 + Alpine.js |
| Realtime | Laravel Reverb |
| Queue | Laravel Horizon |
| Search | Laravel Scout + Meilisearch |
| Charts | ApexCharts 5 + Chart.js 4 |
| Maps | Leaflet 1.9 |
| AI | Claude / OpenAI / Gemini via Dot.Agents |
| Graph DB | PostgreSQL (JSONB relationships) / future: Neo4j |
| Data Warehouse | PostgreSQL 16 (shared infra) |
| Storage | AWS S3 |

---

## Key Database Tables

```
analytics_snapshots         — point-in-time platform data snapshots
intelligence_nodes          — knowledge graph nodes
intelligence_edges          — knowledge graph relationships
business_dna_profiles       — per-organisation DNA model
metric_definitions          — what to measure per platform
computed_metrics            — cached intelligence results
alerts                      — threshold-based intelligence alerts
recommendations             — AI-generated next-best-action items
reports                     — scheduled and ad-hoc report definitions
report_runs                 — report execution history
dashboards                  — user-configured dashboard layouts
dashboard_widgets           — individual widget configurations
data_sources                — registered platform connections
```

---

## Relationship to Other Platforms

- **Not dependent on Dot.Agents** — Dot.Agents is one of many data sources, not a dependency
- **Feeds back into all platforms** — surfaces recommendations within each platform's UI
- **InfoDot hub** — accessible from the Dot Switcher with SSO handoff token

---

## Build Priority

**Phase 1:** Data ingestion layer + core dashboards per platform  
**Phase 2:** Cross-platform correlation engine + Intelligence Graph  
**Phase 3:** Business DNA Model + predictive engines  
**Phase 4:** Decision Intelligence + autonomous recommendations  
**Phase 5:** Industry-specific intelligence engines (Mining, Agriculture, Construction)
