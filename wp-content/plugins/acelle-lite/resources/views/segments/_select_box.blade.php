@if (count($options))
    @include('helpers.form_control', ['include_blank' => trans('messages.choose'), 'type' => 'select', 'name' => 'segment_uid', 'label' => trans('messages.which_segment_send'), 'value' => '', 'options' => $options])
@endif