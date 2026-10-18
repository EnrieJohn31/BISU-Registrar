# BISU seal source

Original campus-hosted PNG, retrieved 2026-10-18 without modifying its pixels:

- [BISU Balilihan campus website](https://bisubalilihan.com/), linked from the [university website](https://bisu.edu.ph/).
- [Source image](https://bisubalilihan.com/wp-content/uploads/2021/03/BISU-LOGO-1.png).
- Public campus media ID: 9045. Retrieved image size: 1600 × 1600. The smaller campus header version displays the same university seal.

The original SHA-256 is recorded in [DEV-8 evidence](../../../docs/DEV-8-BISU-BRANDING.md). The seal is an institutional asset sourced from BISU; no new seal or mark was designed for this application.

Regenerate the committed display/browser assets on Windows from the repository root:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File ./scripts/build-brand-icons.ps1
```

This uses Windows `System.Drawing` to resize and encode the original, preserving its proportions, colors and transparency. It requires no downloaded image tool, runtime CDN or application package. Generated assets are committed; Linux CI does not need to run this maintenance script.
