<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Folder Delimiter Character
    |--------------------------------------------------------------------------
    |
    | Used separating folders/sub-dirs when generating mapped env path
    |   Example:
    |   --------------------
    |   You put 'automap' as value for the config of: config/mammals/room/elephant->permissions->allowed_to_walk (file path is config/mammals/room/elephans.php)
    |   If your folder_delimiter_character is '.', then generated env key will be: "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK"
    |   If your folder_delimiter_character is '@', then generated env key will be: "MAMMALS@ROOM@ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK"
    |   --------------------
    | Rules:
    |   - Non alpha-numeric characters (except those that could mess up regex)
    |   - Has to be char (single character string)
    |   - Suggested values: '.'
    |   - Cannot have the same value with `word_delimiter_character`
    |
    */
    'folder_delimiter_character' => '.',
    'inside_config_delimiter_character' => '.',
    'word_delimiter_character' => '_',
    'register_helpers'=>true,
    // other options...
];