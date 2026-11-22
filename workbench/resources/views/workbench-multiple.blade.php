<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>laravel-htmx-table Multiple Tables</title>
    <link rel="stylesheet" href="/vendor/htmx-table/htmx-table.css">
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.11/dist/htmx.min.js"
          integrity="sha384-2OatzQy1H+Zd/IIrjr1TcuDGqLXeHhbooAyJY1KdQMKnr4LZ22k31GBLdYKHmVjg"
          crossorigin="anonymous"></script>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif
        }

        section + section {
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <main style="max-width: 960px; margin: 2rem auto; padding: 0 1rem;">
        <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
            <a href="/demo-table?mode=paginate">Single table demo</a>
            <a href="/demo-tables">Multiple tables</a>
        </div>

        <p>
            Each table targets its own wrapper with
            <code>hx-target="closest .htmx-table-wrapper"</code>, so both tables
            can search, sort, and paginate independently.
        </p>

        <section>
            <h2>Items</h2>
            {!! $itemsTableHtml !!}
        </section>

        <section>
            <h2>Compact items</h2>
            {!! $compactTableHtml !!}
        </section>
    </main>
</body>
</html>
