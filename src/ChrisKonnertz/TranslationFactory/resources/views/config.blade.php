@extends('translationFactory::base')

@section('title', 'Config')

@section('content')
    <div class="header">
        <h1>Config</h1>

        <p class="page-info-text">
            Below you can see all the configuration values of this package.
        </p>
    </div>

    <div class="table-wrapper">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach($configValues as $configKey => $configValue)
                    <tr>
                        <td>
                            {{ $configKey }}
                        </td>
                        <td>
                            @if ($configValue === '' or $configValue === null or $configValue === [])
                                <em>empty</em>
                            @elseif (is_array($configValue))
                                <span class="label">{!! implode('</span>, <span class="label">', $configValue) !!}</span>
                            @elseif (is_bool($configValue))
                                @if ($configValue)
                                    <span title="True">✓</span>
                                @else
                                    <span title="False">🞪</span>
                                @endif
                            @else
                                {{ $configValue }}
                            @endif
                        </td>
                        <td>
                            {{ gettype($configValue)  }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
