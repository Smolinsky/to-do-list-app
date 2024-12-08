<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{config('app.name')}} API documentation</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="//unpkg.com/swagger-ui-dist@4/swagger-ui.css">
    <script src="//unpkg.com/swagger-ui-dist@4/swagger-ui-bundle.js"></script>
    <script src="//unpkg.com/swagger-ui-dist@4/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "{{ route('specification.schema') }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>

</head>
<body>
<div id="swagger-ui"></div>
</body>
</html>
