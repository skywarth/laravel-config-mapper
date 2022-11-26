*Proof reading for the documentation is pending*

# Laravel Config Mapper

Packagist: https://packagist.org/packages/skywarth/laravel-config-mapper

## Table of Contents

- [Installation](#installation) 
- [Problem Definition](#problem-definition)
  - [Example case](#example-case)
- [How to Use](#how-to-use)
  - [TL;DR](#how-to-use-tl-dr)
  - [Map the env keys by command](#map-env-keys-command)
    - [Just output the mapped env keys](#map-env-keys-command-just-output)
    - [Add mapped env keys to file](#map-env-keys-command-add-mapped-env-keys-to-file)
    - [Update configs and replace](#map-env-keys-command-update-config)
  - [Alternative Config Helper](#alternative-config-helper)
- [Roadmap & TODOs](#roadmap-and-todos)

Laravel Config Mapper is a package for assisting your project with the ability to **automatically map configs with env keys**. It is designed for Laravel framework.

## <a name='installation'></a> Installation

Run:
```
composer require skywarth/laravel-config-mapper
```

Optionally, you may publish the config, which allows you to tinker with libraries settings:
```
php artisan vendor:publish --provider="Skywarth\LaravelConfigMapper\LaravelConfigMapperServiceProvider" --tag="config"
```





## <a name='problem-definition'></a> Problem Definition

You know the hassle... When defining a new configuration or adding to existing configuration, you have to give it a corresponding and appropriate env key. And if your config hierarchy has some depth, it is rather troublesome and prone to error. Laravel Config Mapper can help you eliminate this.

### <a name='example-case'></a> Example Case
    
```
your-laravel-project/
├── config/
│   ├── filesystems.php
│   ├── app.php
│   ├── auth.php
│   ├── queue.php
│   ├── **tiger.php**
│   ├── mammals/
│   │   ├── panda.php
│   │   ├── dog.php
│   │   └── room/
│   │       └── elephant.php
│   └── non-mammals/
│       └── penguin
├── app
├── storage
├── public
├── .env
└── .env.example
```
<p align="center" name="example-folder-structure"><i>Example folder structure</i></p>


For the file structure above, assume you want to create configs for `tiger`, `panda`, `dog`, `elephant` and `penguin` (files are already created).

If we consider `elephant` in the `room` folder, and let's say we want elephant config to have `enabled`, and under `permissions` group `allowed_to_walk` and `allowed_to_sleep`. It would look something like this:
```php
#./config/mammals/room/elephant.php
<?php
return [
    'enabled'=>env('MAMMALS_ROOM_ELEPHANT_ENABLED',1),
    'permissions'=>[
        'allowed_to_walk'=>env('MAMMALS_ROOM_ELEPHANT_PERMISSIONS_ALLOWED_TO_WALK',1),
        'allowed_to_sleep'=>env('MAMMALS_ROOM_ELEPHANT_PERMISSIONS_ALLOWED_TO_SLEEP',1)
    ]
];
```

E.g: `config('mammals.room.elephant.permissions.allowed_to_walk')` for accessing

As you can see, it becomes cumbersome to name env keys after certain folder/path depth. If you ever had to deal with scenarios where you access a config like `config('some.really.deep.down.config.and.also.in.an.array.nice_key_though')` you know how painful it is to write the env key for that. 



## <a name='how-to-use'></a> How to use

There is two distinct application methods for this package:
1. Map the env keys by command 
2. Alternative config helper

### <a name='how-to-use-tl-dr'></a> TL;DR
1. Put `'automap'` value for configs
2. Run `php artisan laravel-config-mapper:publish-env-keys`

### <a name='map-env-keys-command'></a> Map the env keys by command

This method is the recommended way of using this library.

1. Put the value of `'automap'` for those configs that you'd like to map
   1. e.g: <a name='sample-application-of-automap'></a>Sample application of automap
      ```php
      #./config/mammals/room/elephant.php
      <?php
        return [
        'enabled'=>env('MAMMALS_ROOM_ELEPHANT_ENABLED',1),
        'permissions'=>[
            'allowed_to_walk'=>'automap' //NOTICE HERE
            'allowed_to_sleep'=>env('MAMMALS_ROOM_ELEPHANT_PERMISSIONS_ALLOWED_TO_SLEEP',1)
        ]
      ];
      ```
2. Run the dedicated <a name='publish-env-keys-command'></a> command: `php artisan laravel-config-mapper:publish-env-keys`
   1. It'll discover your automap configs
   2. After discovery, it'll prepare respective env keys for these
   3. Then config path & env key pairs will be output
   4. So far no change is made at all to your codebase
3. Select an option on how you'd like to apply
   1. Each option is explained down below

#### <a name='map-env-keys-command-just-output'></a> 1. "Just output the mapped env keys, I'll copy them myself"
This option will just output the env keys that you'll have to use. Just as the name suggests, it doesn't alter any file at all in your codebase.
After receiving the output from the console, you should paste the mapped env keys to your `.env` or wherever you like. Then you should replace all `'automap'` values in config with an `env()` helper function call. 

For the [example](#sample-application-of-automap) above, it would produce this output:
```
--------------COPY BELOW--------------
#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE
MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=
#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE
--------------COPY ABOVE--------------
Don't forget to assign values to your env keys !

```

#### <a name='map-env-keys-command-add-mapped-env-keys-to-file'></a> 2. "Add mapped env keys to file"

This option will print the mapped env keys into a file of your choice.
Returning to the [example](#sample-application-of-automap), when we run the [command](#publish-env-keys-command) and chose this option, and pick `.env` as our target file, we can observe the change in `.env` file:

```
#.env file

APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost


#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE
MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=
#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE
```

If automap env keys section present, the command will update the existing section. Otherwise it'll just append the section to the end of the file. 

You can pick among these options as files:
- .env
- .env.example
- laravel-config-mapper.env

If the chosen file doesn't exist, the command will ask you if you'd like it to be created.


#### <a name='map-env-keys-command-update-config'></a> 3. "Update configs to replace 'automap' values with corresponding env keys, then add mapped keys to file"

This option allows you to be completely independent of config mapper library. It replaces any `'automap'` value placed in configs with appropriate mapped env keys. After that, it'll continue with the same procedure as ["Add mapped env keys to file"](#map-env-keys-command-add-mapped-env-keys-to-file). 

Recalling [example](#sample-application-of-automap), running [command](#publish-env-keys-command) with this choice will produce these changes:

```php
      #./config/mammals/room/elephant.php
      <?php
        return [
        'enabled'=>env('MAMMALS_ROOM_ELEPHANT_ENABLED',1),
        'permissions'=>[
            'allowed_to_walk'=>env('MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK','automap') //NOTICE HERE
            'allowed_to_sleep'=>env('MAMMALS_ROOM_ELEPHANT_PERMISSIONS_ALLOWED_TO_SLEEP',1)
        ]
      ];
```
See the `'allowed_to_walk'` key, it had the value of `'automap'` prior to running the command, but now it is paired to an env key.

And corresponding mapped env keys are added to `.env` file, how convenient. 

```
#.env file

#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE
MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=
#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE
```


---

### <a name='alternative-config-helper'></a> Alternative Config Helper

This method is not the recommended method. Please advise [Map the env keys by command](#map-env-keys-command) method to see if it fits your needs.

Package ships with a new helper function at your disposal. Let's return to the `elephant` config. But this time we'll be using Laravel Config Mapper's helper function `configMapped()`.

```php
#./config/mammals/room/elephant.php
<?php
return [
    'enabled'=>'automap',
    'permissions'=>[
        'allowed_to_walk'=>'automap',
        'allowed_to_sleep'=>env('MAMMALS_ROOM_ELEPHANT_PERMISSIONS_ALLOWED_TO_SLEEP',1)
    ]
];
```
You may have noticed that `enabled` and `allowed_to_walk` configs have the value of `'automap'`. This is due to the fact we are feeling lazy and don't want to ponder about some env key for it.

Now run `php artisan laravel-config-mapper:publish-env-keys` command. In the command, when we select '[1] Just output the mapped env keys, I'll copy them myself' option, it outputs this:
```
4. Copy this and paste it to your env, then edit values as you wish:
--------------COPY BELOW--------------
#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE
MAMMALS.ROOM.ELEPHANT.ENABLED=
MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=
#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE
--------------COPY ABOVE--------------
```
Marvelous, now we can just copy this string and paste it into `.env` file. After pasting it and assigning values, only one thing left to do: use the `configMapped()` instead of `config()`. Because if you use `config('mammals.room.elephant.permissions.allowed_to_walk')`, it'll give you `'automap'` naturally. But if you use `configMapped('mammals.room.elephant.permissions.allowed_to_walk')` It'll automatically find the corresponding env key *(MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK)* and return Its value. 

**Q:** *hurrr durr why do I have to use an alternate config helper ?? How am I supposed to know when to use config() and when to use configMapped()*

**A:** It is better to always use `configMapped()` because it inherently calls `config() ` inside anyways if the corresponding config doesn't have `'automap'` value. In other terms, it is just a wrapper around config helper to grant it additional capabilities. For the example above, I can still access `allowed_to_sleep` config using `configMapped()` even though it is not an automap config.  



## <a name='roadmap-and-todos'></a> Roadmap & TODOs

- [X] ~~Table of content for readme~~
- [X] ~~Assigned values for mapped env keys are lost when written :( Gotta find a way to preserve it~~
- [ ] Refactor the command
- [X] ~~Make helper function registration optional through config~~
- [ ] Unit tests, maybe ?
- [ ] comment blocks for config file
- [ ] Optionally remove redundant keywords from mapped env key. 







