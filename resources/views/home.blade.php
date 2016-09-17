@extends('master')

@section('title', 'Packagist Bot for Slack')

@section('content')
    <h1>Packagist Bot for Slack</h1>
    <p>
        Click the button below to add Packagist Bot to your Slack team. This will add a <code>/packagist</code> command
        to search <a href="//packagist.org" target="_blank">Packagist</a>, the biggest repository of PHP packages directly
        from within Slack.
    </p>

    <a href="https://slack.com/oauth/authorize?scope=commands&client_id={{ getenv('SLACK_CLIENT_ID') }}&redirect_uri={{ getenv('SLACK_REDIRECT_URI') }}" class="no-style">
        <img alt="Add to Slack"
             height="40"
             width="139"
             src="https://platform.slack-edge.com/img/add_to_slack.png"
             srcset="https://platform.slack-edge.com/img/add_to_slack.png 1x, https://platform.slack-edge.com/img/add_to_slack@2x.png 2x"
        />
    </a>

    <h2>Usage</h2>
    <p>
        You can search for <code>vendor/package-name</code> or simply give it a search term. It will always
        return the top result from <a href="//packagist.org" target="_blank">Packagist</a>.
    </p>

    <img src="{{ url('images/demo.png') }}">
@endsection