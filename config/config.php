<?php

/*
     __                                __   ______            _____          __  ___
    / /   ____ __________ __   _____  / /  / ____/___  ____  / __(_)___ _   /  |/  /___ _____  ____  ___  _____
   / /   / __ `/ ___/ __ `/ | / / _ \/ /  / /   / __ \/ __ \/ /_/ / __ `/  / /|_/ / __ `/ __ \/ __ \/ _ \/ ___/
  / /___/ /_/ / /  / /_/ /| |/ /  __/ /  / /___/ /_/ / / / / __/ / /_/ /  / /  / / /_/ / /_/ / /_/ /  __/ /
 /_____/\__,_/_/   \__,_/ |___/\___/_/   \____/\____/_/ /_/_/ /_/\__, /  /_/  /_/\__,_/ .___/ .___/\___/_/
                                                                /____/               /_/   /_/
Skywarth - Initial Release 2022
*/

return [

    //======================================================================
    // DELIMITERS
    //======================================================================
    'delimiters'=>[
        /*
        |--------------------------------------------------------------------------
        | Folder Delimiter Character
        |--------------------------------------------------------------------------
        |
        | Used separating folders/sub-dirs when generating mapped env path
        |   Example:
        |   --------------------
        |   You put 'automap' as value for the config of: config/mammals/room/elephant->permissions->allowed_to_walk (file path is config/mammals/room/elephant.php)
        |   If your folder_delimiter_character is '.', then generated env key will be: "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK"
        |   If your folder_delimiter_character is '_', then generated env key will be: "MAMMALS_ROOM_ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK"
        |   --------------------
        | Rules:
        |   - Alphanumeric characters, '.' (dot) and '_' (underscore) can be used
        |   - Has to be char (single character string)
        |   - Suggested value: '.'
        |
        */
        'folder_delimiter_character' => '.',








        /*
        |--------------------------------------------------------------------------
        | Inside Config Delimiter Character
        |--------------------------------------------------------------------------
        |
        | Used for separating config key's inside the file section when generating mapped env path
        |   Example:
        |   --------------------
        |   You put 'automap' as value for the config of: config/mammals/room/elephant->permissions->allowed_to_walk (file path is config/mammals/room/elephant.php)
        |   If your inside_config_delimiter_character is '.', then generated env key will be: "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK"
        |   If your inside_config_delimiter_character is '_', then generated env key will be: "MAMMALS.ROOM.ELEPHANT_PERMISSIONS_ALLOWED_TO_WALK"
        |   --------------------
        | Rules:
        |   - Alphanumeric characters, '.' (dot) and '_' (underscore) can be used
        |   - Has to be char (single character string)
        |   - Suggested value: '.'
        |
        */
        'inside_config_delimiter_character' => '.',







        /*
        |--------------------------------------------------------------------------
        | Word Delimiter Character
        |--------------------------------------------------------------------------
        |
        | Used for separating each word in file/folder/key names when generating mapped env path
        |   Example:
        |   --------------------
        |   You put 'automap' as value for the config of: config/mammals/room/elephant->permissions->allowed_to_walk (file path is config/mammals/room/elephant.php)
        |   If your inside_config_delimiter_character is '.', then generated env key will be: "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED.TO.WALK"
        |   If your inside_config_delimiter_character is '_', then generated env key will be: "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK"
        |   --------------------
        | Rules:
        |   - Alphanumeric characters, '.' (dot), '_' (underscore) and '' (empty string) can be used
        |   - Has to be char (single character string) or empty string
        |   - Suggested value: '_'
        |
        */
        'word_delimiter_character' => '_',
        // other options...
    ],


    /*
    |--------------------------------------------------------------------------
    | Register Helpers
    |--------------------------------------------------------------------------
    |
    | Toggle for whether to register the helper functions (configMapped()) or not.
    | You may set it to false if you do not plan on using configMapped() helper function
    |
    | Expects boolean value: true/false
    |
    */
    'register_helpers'=>true,


];