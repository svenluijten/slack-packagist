@extends('master')

@section('title', 'Packagist for Slack')

@section('content')
    <p>
        Click the button below to add Packagist for Slack to your team. This will add a <code>/packagist</code> command
        to search <a href="//packagist.org" target="_blank">Packagist</a>, the biggest repository for PHP packages directly
        from within Slack. You can search for <code>vendor/package-name</code> or simply give it a search term. It will always
        return the top result from <a href="//packagist.org" target="_blank">Packagist</a>.
    </p>

    <a href="https://slack.com/oauth/authorize?scope=commands&client_id={{ getenv('SLACK_CLIENT_ID') }}&redirect_uri={{ getenv('SLACK_REDIRECT_URI') }}">
        <img alt="Add to Slack"
             height="40"
             width="139"
             src="https://platform.slack-edge.com/img/add_to_slack.png"
             srcset="https://platform.slack-edge.com/img/add_to_slack.png 1x, https://platform.slack-edge.com/img/add_to_slack@2x.png 2x"
        />
    </a>
@endsection