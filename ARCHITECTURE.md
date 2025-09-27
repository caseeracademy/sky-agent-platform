# Application Architecture

This document serves as the master plan for the application's structure, defining user roles and the modular breakdown of features.

## 1. User Roles & Hierarchy

The system has two main user groups, each with its own internal hierarchy.

- **Admin Hierarchy:**
    - **Super Admin:** The platform owner. Has full system access.
    - **Admin Staff:** Employees of the Super Admin. Can be assigned to manage applications.

- **Agent Hierarchy:**
    - **Agent Owner:** The manager of a recruitment agency. Has full control over their agency's data.
    - **Agent Staff:** Employees of an Agent Owner (e.g., recruiters, accountants). Have limited permissions set by their owner.

## 2. Super Admin Portal Modules

The Super Admin portal is divided into five core modules.

1.  **Dashboard & Reporting:** High-level view of business KPIs, activity feeds, and generation of detailed reports.
2.  **Application Management:** The core workflow for viewing, assigning, and verifying all applications from all agents.
3.  **Financial Management:** Module for approving withdrawal requests and viewing payout histories.
4.  **User Management:** For creating and managing both Agent accounts and internal Admin Staff.
5.  **System Setup:** For creating and managing Universities and Programs, including their commission structures.

## 3. Agent Portal Modules

The Agent portal is divided into five core modules.

1.  **Dashboard:** A personalized overview of an agent's key metrics and recent activity.
2.  **Student Management:** The agent's private CRM for managing their list of students.
3.  **Application Management:** For creating new applications, tracking their status, and uploading documents.
4.  **Commission & Payouts:** For viewing earned commissions and requesting withdrawals.
5.  **Team Management (Owner Only):** An admin panel for the Agent Owner to manage their own staff and permissions.