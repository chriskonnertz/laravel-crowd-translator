@extends('translationFactory::base')

@section('title', 'File')

@section('content')
    <div class="header">
        <h1 title="{{ $translationBag->getBaseFile() }}">{{ $translationBag->getTitle() }}</h1>

        <p class="initial-info-text">
            Choose a translation item from the items below.
            Then you will be able to edit it.
            Translations will be saved automatically.
        </p>
    </div>

    <div class="items-box @php echo $currentItemKey ? '' : 'max' @endphp">
        <div class="divider text-center" data-content="Choose item ({{ sizeof($translationBag->getTranslations(), COUNT_RECURSIVE) }} available)"></div>

        <ul>
            @foreach($translationBag->getTranslations()[$translationBag->getBaseLanguage()] as $itemKey => $itemValue)
                @include('translationFactory::items_list_item')
            @endforeach
        </ul>
    </div>

    <div class="item-box">
        <div class="divider text-center" data-content="Translate item @if ($currentItemKey) {{ '"'.$currentItemKey.'"' }} @endif"></div>

        @if ($currentItemKey)
            <form>
                <div class="form-group">
                    <label class="form-label">Original in <a href="https://www.loc.gov/standards/iso639-2/php/langcodes_name.php?iso_639_1={{ $baseLanguage }}" target="_blank"><i>{{ $baseLanguage }}</i></a>:</label>

                    <blockquote class="bg-gray">
                        @php $originalText = array_get($translationBag->getTranslations()[$translationBag->getBaseLanguage()], $currentItemKey) @endphp
                        <p>{!! preg_replace('/(:\w+|\||\{\d*\}|\[\d*,(\d*|\*)])/',
                        '<span title=":\w+ = parameter, | = choice, {\d*} = exact amount, [\d,\d|*] = range">${1}</span>',
                        htmlspecialchars($originalText)) !!}</p>
                    </blockquote>
                </div>

                <div class="form-group">
                    <label class="form-label" for="translation">Translation to <a href="https://www.loc.gov/standards/iso639-2/php/langcodes_name.php?iso_639_1={{ $targetLanguage }}" target="_blank"><i>{{ $targetLanguage }}</i></a>:</label>

                    {{-- One giant line to avboid issues with whitespace --}}
                    <textarea class="form-input" id="translation" name="translation" placeholder="Please enter your translation here" rows="5">@if($translationBag->hasTranslation($targetLanguage, $currentItemKey)){{ $translationBag->getTranslation($targetLanguage, $currentItemKey) }}@endif</textarea>

                    <div class="toast toast-error save-error d-hide">
                        Could not save the translation. <a href="#">Retry?</a>
                    </div>
                </div>

                <div class="button-bar">
                    <button type="button" class="btn btn-sm btn-clear-form">Clear</button>
                    <button type="reset" class="btn btn-sm btn-reset-form">Reset</button>
                    <button type="button" class="btn btn-sm btn-copy-original">Original</button>
                </div>
            </form>
        @else
            <div class="empty">
                <div class="empty-icon">
                    <i class="icon icon-3x icon-edit"></i>
                </div>
                <p class="empty-title h5">No item selected</p>
                <p class="empty-subtitle">Select an item from the list above to start translating.</p>
            </div>
        @endif
    </div>


    <script>
        (function () {
            var ul = document.querySelector('.items-box ul');

            @if ($currentItemKey)
                var li = ul.querySelector('li[data-key="{{ $currentItemKey }}"]');

                ul.scrollTop = li.offsetTop - ul.offsetTop;
                li.classList.add('current');

                var textArea = document.getElementById('translation');
                textArea.focus();

                var save = function()
                {
                    var request = new XMLHttpRequest();

                    var form = document.querySelector('form');
                    var data = new FormData(form);

                    document.querySelector('.save-error').classList.add('d-hide');

                    request.addEventListener('readystatechange', function() {
                        if (request.readyState === XMLHttpRequest.DONE) {
                            if (request.status !== 200) {
                                document.querySelector('.save-error').classList.remove('d-hide');
                            }
                            if (request.status === 200) {
                                document.querySelector('.save-error').classList.add('d-hide');
                            }
                        }
                    });

                    request.open(
                        'POST',
                        '{{ url('translation-factory/file/'.$translationBag->getHash().'/item/'.$currentItemKey) }}',
                        true
                    );
                    request.send(data);

                };

                document.querySelector('.save-error a').addEventListener('click', function(event)
                {
                    event.preventDefault();
                    save();
                });
                textArea.addEventListener('focusout', save);

                document.querySelector('form .btn-clear-form').addEventListener('click', function(event)
                {
                    textArea.value = '';
                    save();
                });

                document.querySelector('form .btn-reset-form').addEventListener('click', function(event)
                {
                    document.querySelector('form').reset();
                    save();
                });

                document.querySelector('form .btn-copy-original').addEventListener('click', function(event)
                {
                    // Source: https://gist.github.com/CatTail/4174511
                    var decodeHtmlEntity = function(str) {
                        return str.replace(/&#(\d+);/g, function(match, dec) {
                            return String.fromCharCode(dec);
                        });
                    };

                    textArea.value = decodeHtmlEntity('{{ $originalText }}');
                    save();
                });
            @endif

            // When the user clicks on an a-element without the href-attribute set, do nothing
            var noLinks = document.querySelectorAll('a[href=""]');
            noLinks.forEach(function(element)
            {
                element.addEventListener('click', function(event)
                {
                    event.preventDefault();
                });
            });

            var resize = function () {
                var content = document.getElementById('content');
                var header = document.querySelector('#content .header');
                var itemBox = document.querySelector('#content .item-box');

                var li = ul.querySelector('li');
                var maxHeight = window.innerHeight
                    - 20 // content padding top
                    - header.offsetHeight
                    - 40 // header paragraph margin bottom
                    - 20 // items-box ul margin top
                    - 40 // items-box ul margin bottom
                    - itemBox.offsetHeight
                    - 20 // content padding top that is not overlaid by footer
                    - 60 // footer.offsetHeight
                    - 1; // extra offset

                var amount = parseInt(maxHeight / li.offsetHeight);
                var height = Math.max(li.offsetHeight, (amount * li.offsetHeight));

                if (height < 3 * li.offsetHeight) {
                    height = 3 * li.offsetHeight
                }

                ul.style.maxHeight = height + 'px';
            };

            window.addEventListener('resize', function(event) {
                resize();
            });
            // Immediately resize, do not wait until document is fully loaded
            resize();
        })();
    </script>
@endsection
