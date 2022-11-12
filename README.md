Warning: Documentation in progress. WIP for nerds

# Laravel Config Mapper

Laravel Config Mapper is a package for assisting your project with the ability to **automatically map configs with env keys**. It is designed for Laravel framework.

## Problem

You know the hassle. When defining a new configuration or adding to existing configuration, you have to give it a corresponding and appropriate env key. And if your config hierarchy has some depth, it is rather troublesome and prone to error. Laravel Config Mapper can help you mitigate this.

### Example Case

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



## How to use

There is two distinct application methods for this package:
1. Map the env keys by command 
2. Alternative config helper


### <a name='map-env-keys-command'></a> Map the env keys by command




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
You may have noticed that `enabled` and `allowed_to_walk` configs have the value of `'automap'`. This is due to the fact we are feeling lazy not don't want to ponder about some env key for it.

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











php artisan vendor:publish --provider="Skywarth\LaravelConfigMapper\LaravelConfigMapperServiceProvider" --tag="config"
