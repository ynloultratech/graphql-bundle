The appearance of the explorer page can be configured:

````yaml
#config.yml

graphql:
    graphiql:
        title:                'GraphQL API Explorer'
        data_warning:         'Heads up! GraphQL Explorer makes use of your <strong>real</strong>, <strong>live</strong>, <strong>production</strong> data.'
        data_warning_dismissible: true
        data_warning_style:   danger # One of "info"; "warning"; "danger"
        template:             explorer.html.twig
        authentication:
            login_message:        'Start exploring GraphQL API queries using your account’s data now.'
````
