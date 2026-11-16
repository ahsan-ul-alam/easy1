{{-- Browser-side strings for the current language, read by resources/js/ui.js. Encoded so it cannot break out of the tag. --}}
<script type="application/json" id="i18n">{!! json_encode(['locale' => app()->getLocale(), 'messages' => trans('js')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
