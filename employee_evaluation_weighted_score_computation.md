# Employee Evaluation Weighted Score Computation

## Overview

The system computes the employee’s final evaluation score using the **Additive Weighted Sum Formula**, where each evaluation section contributes according to its configured Master Weight.

Current Master Weights:
- KRA Section = 80%
- Behavior Section = 20%

---

# Final Score Formula

```text
Final Score = (KRA Subtotal × 0.80) + (Behavior Average × 0.20)
```

---

# KRA Subtotal Computation

Each KRA criterion contains an individual percentage weight.

The KRA Subtotal is calculated by:
1. Multiplying each KRA rating by its individual weight
2. Summing all weighted values

## Formula

```text
KRA Subtotal = Σ((Individual KRA Weight / 100) × Rating)
```

## Example

| KRA Criterion | Weight | Rating | Weighted Result |
|---|---|---|---|
| Campaign Execution | 20% | 4 | 0.80 |
| Content Strategy | 20% | 4 | 0.80 |
| SEO & SEM | 20% | 4 | 0.80 |
| Lead Generation | 20% | 4 | 0.80 |
| Marketing Analytics | 20% | 4 | 0.80 |

```text
KRA Subtotal = 0.80 + 0.80 + 0.80 + 0.80 + 0.80
KRA Subtotal = 4.00
```

---

# Behavior Average Computation

The Behavior Score is computed by averaging all behavior ratings.

## Formula

```text
Behavior Average = ΣRatings / Total Items
```

## Example

| Behavior Criterion | Rating |
|---|---|
| Positive Attitude | 2 |
| Respect | 2 |
| Accountability | 2 |
| Commitment | 2 |
| Teamwork | 2 |
| Integrity | 2 |
| Continuous Improvement | 2 |
| Excellent Client Experience | 2 |

```text
Behavior Average = (2 + 2 + 2 + 2 + 2 + 2 + 2 + 2) / 8
Behavior Average = 16 / 8
Behavior Average = 2.00
```

---

# Final Weighted Score Computation

The final score combines:
- 80% from KRA
- 20% from Behavior

## Example

Given:
- KRA Subtotal = 4.00
- Behavior Average = 2.00

## Computation

```text
Final Score = (4.00 × 0.80) + (2.00 × 0.20)
Final Score = 3.20 + 0.40
Final Score = 3.60
```

---

# Final Result

| Component | Value |
|---|---|
| KRA Subtotal | 4.00 |
| Behavior Average | 2.00 |
| Final Weighted Score | 3.60 |
| Performance Equivalent | Outstanding |

---

# Expected System Behavior

After HR Manager final approval, the system must:

- Recompute the KRA Subtotal
- Recompute the Behavior Average
- Recompute the Final Weighted Score
- Use the HR Manager’s final edited ratings
- Override previous HR Supervisor-adjusted values
- Display only the latest finalized evaluation values in the Employee Portal

---

# Evaluation Data Priority

The system should follow this evaluation hierarchy:

```text
Employee Self Evaluation
        ↓
HR Supervisor Adjustments
        ↓
HR Manager Final Adjustments
```

Final approved values from the HR Manager must always take priority over previous evaluation stages.

---

# Current Anomaly

## Problem

The Employee Portal still displays:
- "Adjusted by Supervisor" values
- Supervisor-edited ratings
- Old subtotal and final score computations

even after HR Manager modifies and finalizes the evaluation.

## Expected Result

If HR Manager changes all ratings from:
- 2 → 3

Then the Employee Portal should display:
- All ratings = 3
- Updated weighted computations
- Updated total score
- Final HR Manager-approved results

instead of retaining the HR Supervisor-adjusted values.
