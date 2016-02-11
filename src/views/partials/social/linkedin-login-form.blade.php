<button class="btn btn-social btn-linkedin" onClick="linkedinLogin()"> {{ config('stormpath.web.socialProviders.linkedin.name') }}</button>

<script type="text/javascript">

    function buildUrl(baseUrl, queryString) {
        var result = baseUrl;

        if (queryString) {
            var serializedQueryString = '';

            for (var key in queryString) {
                var value = queryString[key];

                if (serializedQueryString.length) {
                    serializedQueryString += '&';
                }

                // Don't include any access_token parameters in
                // the query string as it will be added by LinkedIn.
                if (key === 'access_token') {
                    continue;
                }

                serializedQueryString += key + '=' + encodeURIComponent(value);
            }

            result += '?' + serializedQueryString;
        }

        return result;
    }

    function linkedinLogin() {
        var oauthStateToken = '{{ uniqid() }}';
        var authorizationUrl = 'https://www.linkedin.com/uas/oauth2/authorization';

        var clientId = '{{ config('stormpath.web.socialProviders.linkedin.clientId') }}';
        var redirectUri = '{{ config('stormpath.web.socialProviders.linkedin.callbackUri') }}';

        var scopes = ['r_basicprofile', 'r_emailaddress'];
        var providerScopes = '';

//        providerScopes.forEach(function (scope) {
//            if (scope.length && scopes.indexOf(scope) !== -1) {
//                scopes.push(scope);
//            }
//        });

        window.location = buildUrl(authorizationUrl, {
            response_type: 'code',
            client_id: clientId,
            scope: scopes.join(' '),
            redirect_uri: redirectUri,
            state: oauthStateToken
        });
    }

</script>