{{--
    Self-contained error page for server errors and maintenance: no database,
    settings or built assets, so it still renders when something is broken.
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>{{ __('app.errors.'.$code.'.title') }}</title>
        <style>
            body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; box-sizing: border-box; background: #fafaf9; color: #1c1917; font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', 'Noto Sans Bengali', 'Nirmala UI', sans-serif; text-align: center; }
            p.code { margin: 0; font-size: 14px; font-weight: 600; color: #78716c; }
            h1 { margin: 8px 0 0; font-size: 28px; letter-spacing: -0.02em; }
            p.text { margin: 12px auto 0; max-width: 28rem; color: #57534e; line-height: 1.6; }
            a { display: inline-block; margin-top: 28px; padding: 12px 20px; border-radius: 999px; background: #1c1917; color: #fff; font-size: 14px; font-weight: 600; text-decoration: none; }
        </style>
    </head>
    <body>
        <main>
            <p class="code">{{ \App\Support\Localization::digits($code) }}</p>
            <h1>{{ __('app.errors.'.$code.'.title') }}</h1>
            <p class="text">{{ __('app.errors.'.$code.'.text') }}</p>
            <a href="{{ url('/') }}">{{ __('app.errors.back_home') }}</a>
        </main>
    </body>
</html>
