# Baseline entry templates

Header-only CSV files for typing in the paper sheets from [baseline-kit.pdf](../baseline-kit.pdf). One file per sheet:

| File | Sheet | One row is |
|---|---|---|
| `codes.csv` | A (top of the sheet) | One transaction type per office: `type_code` 1 to 5 or `O`, and the office's own wording |
| `arrivals.csv` | A | One 30-minute block of one day |
| `timed_sample.csv` | B | One client in a timed window. `sampling` is `every` or `every2`; `outcome` is `S`, `L` or `R`; times as `HH:MM` |
| `records_daily.csv` | C1 | One day or week from the office's own count |
| `records_turnaround.csv` | C2 | One completed request: dates and document type only |
| `day_sheet.csv` | D | One office on one day |

Dates are `YYYY-MM-DD`. Never add a name, ID number or receipt number column. Do not commit filled-in files to Git; keep them with the project lead. The repository ignores nothing here, so keep filled copies outside it.
