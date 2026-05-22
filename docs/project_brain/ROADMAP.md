# Roadmap

## Phase 0 - Brain Bootstrap
Status: active
- Inventory discovery and connectivity mapping. (Completed)
- Standardizing network architecture parameters and folder organization. (Completed)

## Phase 1 - Core Delivery & Service Restoration
Status: active / in-progress
- Debugging active tunnel flapping, routing syntax mismatches, and service crashes.
- Stabilizing live multicast internet delivery to edge nodes.

## Phase 2 - Hardening & Performance Optimization
Status: planned
- Resolving server process bottlenecks (srv09 duplicate service conflicts).
- Monitoring reverse-tunnel session latency and client load resilience.

## Phase 3 - Centralized Configuration Automation (CCMS)
Status: planned (Future Deployment)
- Initialize relational MySQL database schema `idn_orchestrator` on Server 07.
- Implement python-based `idn-ctl` compiler to auto-render Xray/HAProxy configurations from Jinja2 templates.
- Integrate zero-touch safe deployment validations and Technitium API sync.

## Milestone completion rule
No milestone is complete without matching test/checklist gates.
