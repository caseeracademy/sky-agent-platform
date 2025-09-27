# Testing Strategy

This document defines the rules and best practices for writing automated tests for the application. Our goal is to ensure code reliability and prevent regressions.

## Framework & Type

- All tests will be written using **Pest**.
- We will primarily focus on **Feature tests**.

## The Golden Rule of Testing

**Every new route or action must have a corresponding Feature test.**

---

## The Four Pillars of a Great Feature Test

To ensure complete test coverage for any new feature, every test file must verify the following four pillars:

**1. The Authorization Test (The Gatekeeper):**
   - **Question:** Is the user allowed to be here?
   - **Action:** Test that unauthenticated users are redirected to the login page. Test that a user with the *wrong role* (e.g., an Agent trying to access a Super Admin page) is forbidden (receives a 403 error).

**2. The Validation Test (The Bouncer):**
   - **Question:** Does the feature reject bad data?
   - **Action:** For any form submission, test that sending invalid or missing data (e.g., creating a student with no name) fails with a validation error and does **not** create a record in the database.

**3. The Happy Path Test (The Main Event):**
   - **Question:** Does the feature work perfectly with valid data?
   - **Action:** Test the intended, successful workflow. For example, a logged-in Agent with the correct role and valid data **can** successfully create a new student, is redirected to the correct page, and you can assert that the student now exists in the database.

**4. The Data Scoping Test (The Privacy Guard):**
   - **Question:** Can one user see or affect another user's data?
   - **Action:** This is critical for our app. For example, create two agents (Agent A and Agent B) and a student for Agent A. Then, write a test where you are logged in as Agent B and try to view or edit the student belonging to Agent A. The test must confirm this action is forbidden.