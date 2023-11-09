@php
if($settings['overrideTitle']) {
    $pageTitle = $settings['overrideTitle'];
} else {
    $pageTitle = trans('common.edit_item_', ['item' => $settings['subject_column'] ? parse_attr($form['data']->{$settings['subject_column']}) : trans('common.item')]) . config('default.page_title_delimiter') . $settings['title'];
}
@endphp

@extends($settings['guard'].'.layouts.default')

@section('page_title', $pageTitle . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
    <div class="w-full" @onclicktab="window.appSetImageUploadHeight()">
        <div class="relative bg-white p-4 lg:p-6">
            <div
                class="mb-3">
                <div class="w-full flex flex-row items-center justify-between">
                    @if($settings['list'])
                        <div class="mb-5">
                            <a href="{{ route($settings['guard'].'.data.list', ['name' => $dataDefinition->name]) }}"
                                class="ll-back-btn w-fit flex text-sm items-center justify-start">
                                <x-ui.icon icon="left" class="mr-2" />
                                {{ trans('common.back_to_list') }}
                            </a>
                        </div>
                    @endif

                    <div class="flex flex-row items-center justify-end">
                        @if ($settings['view'])
                            <a href="{{ route($settings['guard'].'.data.view', ['name' => $dataDefinition->name, 'id' => $form['data']->id]) }}"
                                class="w-full flex items-center btn-sm text-sm mr-2 btn-primary ll-primary-btn">
                                <x-ui.icon class="h-3.5 w-3.5 mr-2" icon="magnifying-glass" />
                                {{ trans('common.view') }}
                            </a>
                        @endif
                        @if ($settings['delete'])
                            <button type="button" class="w-full flex items-center btn-sm btn-danger text-sm ll-danger-btn"
                                @click="deleteItem('{{ $form['data']->id }}', '{{ $settings['subject_column'] ? str_replace("'", "\'", parse_attr($form['data']->{$settings['subject_column']})) : null }}')">
                                <x-ui.icon class="h-3.5 w-3.5 mr-2" icon="trash" />
                                {{ trans('common.delete') }}
                            </button>
                        @endif
                    </div>
                </div>

                <div class="w-full flex items-center space-x-3">
                    @if($settings['list'])
                        <a href="{{ route($settings['guard'].'.data.list', ['name' => $dataDefinition->name]) }}">
                    @endif
                        <h5 class="dark:text-white font-semibold flex items-center">
                            @if($settings['icon'])
                                <x-ui.icon :icon="$settings['icon']" class="inline-block w-5 h-5 mr-2 dark:text-white" />
                            @endif
                            @if($settings['overrideTitle'])
                                {!! $settings['overrideTitle'] !!}
                            @else
                                {!! $settings['title'] !!}
                            @endif
                        </h5>
                    @if($settings['list'])
                        </a>
                    @endif
                    @if(!$settings['overrideTitle'])
                        <div class="text-gray-400 font-medium">
                            {{ trans('common.edit_item_', ['item' => $settings['subject_column'] ? parse_attr($form['data']->{$settings['subject_column']}) : trans('common.item')]) }}
                        </div>
                    @endif
                </div>
            </div>
            @php
            $hasTabs = !empty($form['tabs']);
            @endphp
            <div class="@if($hasTabs) pt-1 @endif">
                <x-forms.messages class="mt-4" />
                <x-forms.form-open
                    :novalidate="$hasTabs"
                    action="{{ route($settings['guard'].'.data.edit.post', ['name' => $dataDefinition->name, 'id' => $form['data']->id]) }}"
                    enctype="multipart/form-data" id="formDataDefinition" method="POST" class="ll-user-add-form space-y-4 md:space-y-6" />
                    @if ($form['columns'])
                        <div class="ll-user-add-form-inputs grid grid-cols-7 gap-x-5">
                            @if($hasTabs)
                                <x-ui.tabs :tabs="array_values($form['tabs'])" active-tab="1" class="space-y-4 md:space-y-6 py-6">
                                @php
                                $previousTab = null;
                                @endphp
                                @foreach ($form['columns'] as $column)
                                    @if (!$column['hidden'])
                                        @if($column['tab'] && $column['tab'] !== $previousTab)
                                            @if($previousTab !== null)
                                                </x-slot>
                                            @endif
                                            <x-slot :name="$column['tab']">
                                        @endif

                                        @if($column['container_start::insert'])
                                            <div class="{{ $column['container_start::insert'] }}">
                                        @endif
                                        @if($column['classes::insert'])
                                            <div class="{{ $column['classes::insert'] }}">
                                        @endif
                                        @include('data.form', compact('form', 'column'))
                                        @if($column['classes::insert'])
                                            </div>
                                        @endif
                                        @if($column['container_end::insert'])
                                            </div>
                                        @endif
                                        @php
                                        $previousTab = $column['tab'];
                                        @endphp
                                    @endif
                                @endforeach
                                    </x-slot>
                                </x-ui.tabs>
                            @else
                                @foreach ($form['columns'] as $column)
                                    @if (!$column['hidden'])
                                        @if($column['container_start::insert'])
                                            <div class="{{ $column['container_start::insert'] }}">
                                        @endif
                                        @if($column['classes::insert'])
                                            <div class="{{ $column['classes::insert'] }}">
                                        @endif
                                        @include('data.form', compact('form', 'column'))
                                        @if($column['classes::insert'])
                                            </div>
                                        @endif
                                        @if($column['container_end::insert'])
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                            
                            @if ($settings['editRequiresPassword'])
                                <div class="p-6 text-sm rounded-lg bg-gray-50 dark:bg-gray-900 border dark:border-gray-700">
                                    <x-forms.input 
                                        value=""
                                        class-label="mb-4"
                                        type="password"
                                        name="current_password_required_to_save_changes"
                                        icon="key"
                                        :label="trans('common.current_password_to_save_changes')"
                                        :placeholder="trans('common.current_password')"
                                        :required="true"
                                    />
                                </div>
                            @endif
                        </div>
                        <div class="grid grid-cols-7 ll-user-add-form-footer gap-x-5">
                            <div class="ll-user-form-empty"></div>
                            @if($settings['list'])
                                <div class="col-span-3">
                                    <a href="{{ route($settings['guard'].'.data.list', ['name' => $dataDefinition->name]) }}"
                                    class="btn w-full ll-btn-white">{{ trans('common.cancel') }}</a>
                                </div>
                            @endif
                            <div class="col-span-3">
                                <button type="submit" class="w-full btn-primary ll-primary-btn" onmouseover="this.style.background='blue';" onmouseout="this.style.background='#2963FF';">{{ trans('common.save_changes') }}<span
                                class="form-dirty hidden">&nbsp;*</span></button>
                            </div>
                        </div>
                    @endif
                <x-forms.form-close />
                @if (session('current_tab_index'))
                    <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        window.openTab({{ session('current_tab_index') }});
                    });
                    </script>
                @endif
                @if ($errors->any())
                    <script>
                    document.addEventListener("DOMContentLoaded", () => {
                        window.openTabWithInvalidElement();
                    });
                    </script>
                @endif
            </div>
        </div>
    </div>

    @if ($settings['delete'])
        <script>
            function deleteItem(id, item) {
                if (item == null) item = "{{ trans('common.this_item') }}";

                appConfirm('{{ trans('common.confirm_deletion') }}', _lang.delete_confirmation_text.replace(":item",
                    '<strong>' + item + '</strong>'), {
                    'btnConfirm': {
                        'click': function() {
                            // Submit form
                            const form = document.getElementById('formDataDefinition');
                            form.action =
                                '{{ route($settings['guard'].'.data.delete.post', ['name' => $dataDefinition->name]) }}/' + id;
                            form.submit();
                        }
                    }
                });
            }
        </script>
    @endif
    
    <script>
        $(document).ready(function() {
            const pathsToCheck = ["/networks/edit"];
            const currentPathname = window.location.pathname;
            const formInputs = $('.ll-user-add-form-inputs');
            const childDivs = formInputs.children('div');
            const divCount = childDivs.length;
            
            if (pathsToCheck.some(path => currentPathname.includes(path))) {
                $('body').addClass('networks-edit-page');
            }else{
                childDivs.eq(0).wrap('<div class="ll-upload-img"></div>');

                const secondDivCount = Math.floor((divCount - 1) / 2);
                const thirdDivCount = divCount - 1 - secondDivCount;

                childDivs.slice(1, secondDivCount + 1).wrapAll('<div class="col-span-3"></div>');
                childDivs.slice(secondDivCount + 1).wrapAll('<div class="col-span-3"></div>');
            }
        });
    </script>
@stop
