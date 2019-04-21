<?php

return [
    /*
    |---------------------------------------------------------------------------
    | Table column format
    |---------------------------------------------------------------------------
    |
    | This regular expression is used to validate table columns name.
    | 
    */
    'table_column_format' => '/^[a-z]([a-z0-9_]*[a-z0-9])?$/',
    
    /*
    |---------------------------------------------------------------------------
    | Model FullName format
    |---------------------------------------------------------------------------
    |
    | This regular expression is used to validate model FullName.
    | 
    */
    'model_fullname_format' => '/^((([A-Z][a-z]+)+)\\\\)*(([A-Z][a-z]+)+)$/',
    
    /*
    |---------------------------------------------------------------------------
    | Models directory
    |---------------------------------------------------------------------------
    |
    | Store models in a subdirectory of /app.
    | This is usefull when dealing with numerous and/or nested models.
    |
    | empty|false : No subdirectory
    | true        : Models will be stored into /app/Models
    | string      : Models will be stored into /app/[ProvidedValue]
    |
    */
    'models-directory' => false,
    
    /*
    |---------------------------------------------------------------------------
    | crud:api theme options
    |---------------------------------------------------------------------------
    */
    'crud:api' => [
        /*
        |---------------------------------------------------------------------------
        | Default layout
        |---------------------------------------------------------------------------
        |
        | If, false the theme won't be registred into application.
        |
        */
        'enabled' => true,
    ],
    
    /*
    |---------------------------------------------------------------------------
    | crud:classic theme options
    |---------------------------------------------------------------------------
    */
    'crud:classic' => [
        /*
        |---------------------------------------------------------------------------
        | Default layout
        |---------------------------------------------------------------------------
        |
        | If, false the theme won't be registred into application.
        |
        */
        'enabled' => true,
        
        /*
        |---------------------------------------------------------------------------
        | Default layout
        |---------------------------------------------------------------------------
        |
        | The layout to extend into generated views.
        | Spedified layout should extend default theme layout.
        | This setting can be overrided using --layout option when invoking the theme command.
        | 
        | empty : use default theme layout.
        |
        */
        'layout' => null,
    ],
];
