@extends('layouts.app')
@section('title', __('Telegram Templates'))

@section('content')
    <!-- Page Header -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            {{ __('Telegram Templates') }}
        </h1>
    </section>

    <!-- Main Content -->
    <section class="content">
        {!! Form::open(['url' => route('telegram_template.update'), 'method' => 'post']) !!}

        <div class="row">
            @foreach ($templates as $template)
                <div class="col-md-12 mb-4">
                    @component('components.widget', [
                        'class' => 'box-primary',
                        'title' => $template->label ?? ucfirst(str_replace('_', ' ', $template->name)),
                    ])
                        <!-- Greeting -->
                        <div class="form-group">
                            {!! Form::label("greeting_{$template->id}", __('Greeting')) !!}
                            {!! Form::text("templates[{$template->id}][greeting]", $template->greeting, [
                                'class' => 'form-control',
                                'id' => "greeting_{$template->id}",
                            ]) !!}
                        </div>

                        <!-- Body -->
                        <div class="form-group">
                            {!! Form::label("body_{$template->id}", __('Body')) !!}
                            {!! Form::textarea("templates[{$template->id}][body]", $template->body, [
                                'class' => 'form-control ckeditor',
                                'id' => "body_{$template->id}",
                            ]) !!}
                        </div>

                        <!-- Footer -->
                        <div class="form-group">
                            {!! Form::label("footer_{$template->id}", __('Footer')) !!}
                            {!! Form::text("templates[{$template->id}][footer]", $template->footer, [
                                'class' => 'form-control',
                                'id' => "footer_{$template->id}",
                            ]) !!}
                        </div>

                        <!-- Auto-send -->
                        <div class="form-group">
                            <label>
                                {!! Form::checkbox("templates[{$template->id}][auto_send]", 1, $template->auto_send) !!}
                                {{ __('Auto-send') }}
                            </label>
                        </div>

                        <!-- Available Tags -->
                        <div class="form-group tw-bg-gray-100 tw-p-2 tw-rounded tw-mb-2">
                            <strong>{{ __('Available Tags') }}</strong>
                            <p>{{ __('You can use these placeholders:') }}</p>
                            <ul>
                                <li><code>@{{ user_name }}</code></li>
                                <li><code>@{{ order_id }}</code></li>
                                <li><code>@{{ business_name }}</code></li>
                                <li><code>@{{ amount }}</code></li>
                                <li><code>@{{ business_phone }}</code></li>
                            </ul>
                        </div>
                    @endcomponent
                </div>
            @endforeach
        </div>

        <!-- Submit -->
        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit" class="tw-dw-btn tw-dw-btn-error tw-dw-btn-lg tw-text-white">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </div>

        {!! Form::close() !!}
    </section>
@stop

@section('javascript')
    <script>
        $('textarea.ckeditor').each(function() {
            let editor_id = $(this).attr('id');
            tinymce.init({
                selector: 'textarea#' + editor_id,
                height: 300,
                menubar: false,
                plugins: 'lists link table code',
                toolbar: 'undo redo | bold italic underline | bullist numlist | alignleft aligncenter alignright | code',
                content_style: "body { line-height: 1.0; font-size: 16px; font-family: 'Inter', sans-serif; }"
            });
        });
    </script>
@endsection
