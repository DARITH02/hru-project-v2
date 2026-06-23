<style>
    .doc-page {
        display: grid;
        gap: 18px;
        padding: 24px;
        color: var(--text)
    }

    .doc-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: linear-gradient(135deg, var(--surface), var(--surface2));
        padding: 18px;
        box-shadow: var(--shadow-sm)
    }

    .doc-eyebrow {
        font-family: var(--font-mono);
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--accent);
        margin: 0 0 6px
    }

    .doc-title {
        font-family: var(--font-display);
        font-size: 32px;
        line-height: 1.08;
        font-weight: 900;
        color: var(--text);
        margin: 0
    }

    .doc-subtitle {
        font-size: 14px;
        color: var(--muted);
        margin: 7px 0 0;
        max-width: 640px
    }

    .doc-stats {
        display: flex;
        gap: 10px;
        flex-wrap: wrap
    }

    .doc-stat {
        min-width: 92px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface);
        padding: 10px 12px;
        box-shadow: var(--shadow-sm)
    }

    .doc-stat.is-pending {
        border-color: color-mix(in srgb, var(--amber) 30%, var(--border))
    }

    .doc-stat.is-approved {
        border-color: color-mix(in srgb, var(--green) 30%, var(--border))
    }

    .doc-stat.is-rejected {
        border-color: color-mix(in srgb, var(--red) 28%, var(--border))
    }

    .doc-stat strong {
        display: block;
        font-size: 22px;
        line-height: 1;
        color: var(--text)
    }

    .doc-stat span {
        display: block;
        margin-top: 5px;
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted)
    }

    .doc-stat.is-pending strong {
        color: var(--amber)
    }

    .doc-stat.is-approved strong {
        color: var(--green)
    }

    .doc-stat.is-rejected strong {
        color: var(--red)
    }

    .doc-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface);
        padding: 12px;
        box-shadow: var(--shadow-sm)
    }

    .doc-search {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 260px;
        flex: 1
    }

    .doc-input,
    .doc-select,
    .doc-textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface2);
        color: var(--text);
        outline: none
    }

    .doc-input:focus,
    .doc-select:focus,
    .doc-textarea:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--accent) 14%, transparent);
        background: var(--surface)
    }

    .doc-input,
    .doc-select {
        height: 40px;
        padding: 0 12px
    }

    .doc-textarea {
        min-height: 92px;
        padding: 10px 12px;
        resize: vertical
    }

    .doc-tabs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap
    }

    .doc-tab,
    .doc-btn,
    .doc-link {
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text2);
        padding: 0 12px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        text-decoration: none;
        cursor: pointer
    }

    .doc-tab:hover,
    .doc-btn:hover,
    .doc-link:hover {
        border-color: var(--accent);
        color: var(--accent);
        background: color-mix(in srgb, var(--accent) 7%, var(--surface))
    }

    .doc-tab.is-active,
    .doc-btn.is-primary {
        background: var(--accent);
        border-color: var(--accent);
        color: #fff
    }

    .doc-tab.is-active:hover,
    .doc-btn.is-primary:hover,
    .doc-btn.is-green:hover,
    .doc-btn.is-red:hover {
        color: #fff;
        filter: brightness(.98)
    }

    .doc-btn.is-green {
        background: var(--green);
        border-color: var(--green);
        color: #fff
    }

    .doc-btn.is-red {
        background: var(--red);
        border-color: var(--red);
        color: #fff
    }

    .doc-btn.is-muted {
        background: var(--surface2)
    }

    .doc-panel {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface);
        box-shadow: var(--shadow-sm);
        overflow: hidden
    }

    .doc-table-wrap {
        overflow-x: auto
    }

    .doc-table {
        width: 100%;
        min-width: 860px;
        border-collapse: collapse
    }

    .doc-table th {
        background: var(--surface2);
        color: var(--muted);
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        text-align: left;
        padding: 12px 14px;
        border-bottom: 1px solid var(--border)
    }

    .doc-table td {
        padding: 14px;
        border-bottom: 1px solid var(--border);
        vertical-align: top;
        color: var(--text2);
        font-size: 13px
    }

    .doc-table tbody tr {
        transition: background .16s ease
    }

    .doc-table tbody tr:hover {
        background: color-mix(in srgb, var(--accent) 4%, transparent)
    }

    .doc-table tr:last-child td {
        border-bottom: 0
    }

    .doc-group-row td {
        background: color-mix(in srgb, var(--accent) 8%, var(--surface2));
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        padding: 11px 14px
    }

    .doc-table tbody tr.doc-group-row:hover {
        background: transparent
    }

    .doc-group-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px
    }

    .doc-group-title strong {
        color: var(--text);
        font-size: 14px
    }

    .doc-group-title span {
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--accent)
    }

    .doc-file {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        min-width: 0
    }

    .doc-file-badge {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase
    }

    .doc-file-badge.is-pdf {
        background: color-mix(in srgb, var(--red) 12%, transparent);
        color: var(--red)
    }

    .doc-file-badge.is-doc,
    .doc-file-badge.is-docx {
        background: color-mix(in srgb, var(--accent) 12%, transparent);
        color: var(--accent)
    }

    .doc-file-badge.is-ppt,
    .doc-file-badge.is-pptx {
        background: color-mix(in srgb, var(--orange) 14%, transparent);
        color: var(--orange)
    }

    .doc-file-title {
        display: block;
        font-weight: 800;
        color: var(--text);
        line-height: 1.25
    }

    .doc-file-meta {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 12px
    }

    .doc-review form {
        display: grid;
        gap: 7px
    }

    .doc-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .05em
    }

    .doc-status.is-pending {
        background: color-mix(in srgb, var(--amber) 14%, transparent);
        color: var(--amber)
    }

    .doc-status.is-approved {
        background: color-mix(in srgb, var(--green) 14%, transparent);
        color: var(--green)
    }

    .doc-status.is-rejected {
        background: color-mix(in srgb, var(--red) 12%, transparent);
        color: var(--red)
    }

    .doc-actions {
        display: flex;
        gap: 7px;
        flex-wrap: wrap
    }

    .doc-review {
        display: grid;
        gap: 8px;
        min-width: 220px
    }

    .doc-empty {
        padding: 34px;
        text-align: center;
        color: var(--muted)
    }

    .doc-form {
        display: grid;
        gap: 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface);
        padding: 18px;
        box-shadow: var(--shadow-sm);
        max-width: 760px
    }

    .doc-field {
        display: grid;
        gap: 6px
    }

    .doc-label {
        font-family: var(--font-mono);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted)
    }

    .doc-alert {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface2);
        padding: 12px;
        color: var(--text2)
    }

    .doc-alert.is-error {
        border-color: color-mix(in srgb, var(--red) 30%, var(--border));
        background: color-mix(in srgb, var(--red) 8%, transparent);
        color: var(--red)
    }

    .doc-alert.is-success {
        border-color: color-mix(in srgb, var(--green) 30%, var(--border));
        background: color-mix(in srgb, var(--green) 8%, transparent);
        color: var(--green)
    }

    @media (max-width:760px) {
        .doc-page {
            padding: 16px
        }

        .doc-title {
            font-size: 26px
        }

        .doc-search {
            min-width: 100%
        }

        .doc-toolbar {
            align-items: stretch
        }

        .doc-tabs {
            width: 100%
        }

        .doc-tab {
            flex: 1
        }

        .doc-stat {
            flex: 1
        }
    }
</style>
