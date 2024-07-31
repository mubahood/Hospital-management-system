<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
        @if (config('admin.show_environment'))
            <strong>Env</strong>&nbsp;&nbsp; {!! config('app.env') !!}
        @endif

        &nbsp;&nbsp;&nbsp;&nbsp;

        @if (config('admin.show_version'))
            <strong>Version</strong>&nbsp;&nbsp; {!! \Encore\Admin\Admin::VERSION !!}
        @endif
        Powered By
        <b><a class="nav-link d-inline-block p-0 text-primary" href="https://8technologies.net" target="_blank"
                rel="noopener">{{ env('APP_NAME', 'GlobalHealth') }} (c) 2024</a></b>
    </div>
    <!-- Default to the left -->
    <p class="nav d-block    text-md-start pb-2 pb-lg-0 mb-0">
        {{-- Powered ❤️ by
        <b><a class="nav-link d-inline-block p-0 text-primary" href="https://twitter.com/8TechConsults"
            target="_blank" rel="noopener">env('APP_NAME', 'GlobalHealth') (c) 2024</a></b> --}}
    </p>
</footer>
