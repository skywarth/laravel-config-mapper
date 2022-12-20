<?php

namespace Skywarth\LaravelConfigMapper\Tests\Feature\PublishMappedEnvKeys;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
use Skywarth\LaravelConfigMapper\Utility;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class AddMappedEnvKeysToFileTest extends AbstractPublishMappedEnvKeys
{

    private function getInitialEnvFileState(){
        return
//DON'T ALTER
"WHADDUP=cool
FOO=BAR

FOOZ=TUNEZ
#SOME random comment line

DISEASE=LIGMA";
    }

    private function setEnvFile(string $filename,string $content){
        File::put(base_path().'/'.$filename,$content);
    }

    private function getEnvFile(string $filename){
        return File::get(base_path().'/'.$filename);
    }


    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $this->setEnvFile('.env',$this->getInitialEnvFileState());

    }

    public function tearDown(): void
    {
        parent::tearDown();
        if(File::exists(base_path().'/.env')){
            File::delete(base_path().'/.env');
        }
        if(File::exists(base_path().'/laravel-config-mapper.env')){
            File::delete(base_path().'/laravel-config-mapper.env');
        }


    }


    public function test_add_to_dot_env_clean(){

        $expected = "\n#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE\n";
        $expected .= "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=\n";
        $expected .= "NON_MAMMALS.PENGUIN.SLIDE=\n";
        $expected .= "BLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT=\n";
        $expected .= "#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE";

        $concatted=$this->getInitialEnvFileState().$expected;

        $applyChoices=[
            1=>"Just output the mapped env keys, I'll copy them myself",
            2=>"Add mapped env keys to file",
            3=>"Update configs to replace 'automap' values with corresponding env keys, then add mapped keys to file",
        ];

        $outputFileOptions=[
            1=>".env",
            2=>".env.example",
            3=>"laravel-config-mapper.env",
        ];

        $configs=ConfigMapper::getAllConfigsArray();

        $automapConfigs=ConfigMapper::filterOutNonAutomapConfigs($configs);
        $normalizedAutomapConfigs=Utility::flatten($automapConfigs);
        foreach ($normalizedAutomapConfigs as $configPath=>$value){
            $mappedEnvKey=ConfigMapper::getMappedEnvKey($configPath);
            $normalizedAutomapConfigs[$configPath]=$mappedEnvKey;
        }



        $this->artisan('laravel-config-mapper:publish-env-keys')
            ->expectsOutput('Discovering config files')
            ->expectsOutput('Filtering non-automap configs out')
            ->expectsOutput('Automap configs below:')
            ->expectsTable(
                ['Config Path','Automap Env key'],
                [//order is extremely important
                    ['config_path'=>'mammals.room.elephant.permissions.allowed_to_walk','mapped_env_key'=>'MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK'],
                    ['config_path'=>'non-mammals.penguin.slide','mapped_env_key'=>'NON_MAMMALS.PENGUIN.SLIDE'],
                    ['config_path'=>'blasphemous-root-config.you-got-a-licence-for-that','mapped_env_key'=>'BLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT'],
                ]
            )
            ->expectsConfirmation('Is config paths and mapped env keys suitable ?','yes')
            ->expectsChoice('How would you like to proceed ?',$applyChoices[2],$applyChoices)
            ->expectsChoice("Append mapped env keys to which file ? (It'll append or overwrite mapped keys only, other data will be kept as it is) (Relative to base path)",$outputFileOptions[1],$outputFileOptions)
            ->expectsOutput("File doesn't contain mapped env keys, appending to the end of the file")
            ->assertSuccessful();





        $this->assertEquals($concatted,$this->getEnvFile('.env'));


    }

    public function test_add_to_dot_env_dirty(){//for env file that already contains mapped env keys

        $dirtyEnvFile=$this->getInitialEnvFileState();
        $dirtyEnvFile .= "\n#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE\n";
        $dirtyEnvFile .= "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=some_already_assigned_value\n";
        $dirtyEnvFile .= "MAMMALS.CAT=this-one-doesnt-exist-so-it-should-be-deleted\n";
        $dirtyEnvFile .= "BLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT=yetAnotherValue-here\n";
        $dirtyEnvFile .= "#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE\n";
        $dirtyEnvFile .= "\n \n";
        $dirtyEnvFile.="SOME_TOTALLY_UNRELATED_KEY_AFTER_AUTOMAP_KEYS=hello\n";
        $dirtyEnvFile.="BRUEH_DEDICATION=EL_MOMENTO_DE_BRUH\n";


        $this->setEnvFile('.env',$dirtyEnvFile);




        $expected=$this->getInitialEnvFileState();
        $expected .= "\n#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE\n";
        $expected .= "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=some_already_assigned_value\n";
        $expected .= "NON_MAMMALS.PENGUIN.SLIDE=\n";
        $expected .= "BLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT=yetAnotherValue-here\n";
        $expected .= "#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE\n";
        $expected .= "\n \n";
        $expected.="SOME_TOTALLY_UNRELATED_KEY_AFTER_AUTOMAP_KEYS=hello\n";
        $expected.="BRUEH_DEDICATION=EL_MOMENTO_DE_BRUH\n";

        $applyChoices=[
            1=>"Just output the mapped env keys, I'll copy them myself",
            2=>"Add mapped env keys to file",
            3=>"Update configs to replace 'automap' values with corresponding env keys, then add mapped keys to file",
        ];

        $outputFileOptions=[
            1=>".env",
            2=>".env.example",
            3=>"laravel-config-mapper.env",
        ];

        $configs=ConfigMapper::getAllConfigsArray();

        $automapConfigs=ConfigMapper::filterOutNonAutomapConfigs($configs);
        $normalizedAutomapConfigs=Utility::flatten($automapConfigs);
        foreach ($normalizedAutomapConfigs as $configPath=>$value){
            $mappedEnvKey=ConfigMapper::getMappedEnvKey($configPath);
            $normalizedAutomapConfigs[$configPath]=$mappedEnvKey;
        }



        $this->artisan('laravel-config-mapper:publish-env-keys')
            ->expectsOutput('Discovering config files')
            ->expectsOutput('Filtering non-automap configs out')
            ->expectsOutput('Automap configs below:')
            ->expectsTable(
                ['Config Path','Automap Env key'],
                [//order is extremely important
                    ['config_path'=>'mammals.room.elephant.permissions.allowed_to_walk','mapped_env_key'=>'MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK'],
                    ['config_path'=>'non-mammals.penguin.slide','mapped_env_key'=>'NON_MAMMALS.PENGUIN.SLIDE'],
                    ['config_path'=>'blasphemous-root-config.you-got-a-licence-for-that','mapped_env_key'=>'BLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT'],
                ]
            )
            ->expectsConfirmation('Is config paths and mapped env keys suitable ?','yes')
            ->expectsChoice('How would you like to proceed ?',$applyChoices[2],$applyChoices)
            ->expectsChoice("Append mapped env keys to which file ? (It'll append or overwrite mapped keys only, other data will be kept as it is) (Relative to base path)",$outputFileOptions[1],$outputFileOptions)
            ->expectsOutput("File already contains mapped env keys, replacing appropriately")
            ->assertSuccessful();





        $this->assertEquals($expected,$this->getEnvFile('.env'));

    }


    public function test_add_to_non_existing_file(){

        $targetFilePath=base_path().'/laravel-config-mapper.env';

        $expected = "\n#[AUTOMAP ENV KEYS BEGIN] - DON'T ALTER THIS LINE\n";
        $expected .= "MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK=\n";
        $expected .= "NON_MAMMALS.PENGUIN.SLIDE=\n";
        $expected .= "BLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT=\n";
        $expected .= "#[AUTOMAP ENV KEYS END] - DON'T ALTER THIS LINE";


        $applyChoices=[
            1=>"Just output the mapped env keys, I'll copy them myself",
            2=>"Add mapped env keys to file",
            3=>"Update configs to replace 'automap' values with corresponding env keys, then add mapped keys to file",
        ];

        $outputFileOptions=[
            1=>".env",
            2=>".env.example",
            3=>"laravel-config-mapper.env",
        ];

        $configs=ConfigMapper::getAllConfigsArray();

        $automapConfigs=ConfigMapper::filterOutNonAutomapConfigs($configs);
        $normalizedAutomapConfigs=Utility::flatten($automapConfigs);
        foreach ($normalizedAutomapConfigs as $configPath=>$value){
            $mappedEnvKey=ConfigMapper::getMappedEnvKey($configPath);
            $normalizedAutomapConfigs[$configPath]=$mappedEnvKey;
        }



        $this->artisan('laravel-config-mapper:publish-env-keys')
            ->expectsOutput('Discovering config files')
            ->expectsOutput('Filtering non-automap configs out')
            ->expectsOutput('Automap configs below:')
            ->expectsTable(
                ['Config Path','Automap Env key'],
                [//order is extremely important
                    ['config_path'=>'mammals.room.elephant.permissions.allowed_to_walk','mapped_env_key'=>'MAMMALS.ROOM.ELEPHANT.PERMISSIONS.ALLOWED_TO_WALK'],
                    ['config_path'=>'non-mammals.penguin.slide','mapped_env_key'=>'NON_MAMMALS.PENGUIN.SLIDE'],
                    ['config_path'=>'blasphemous-root-config.you-got-a-licence-for-that','mapped_env_key'=>'BLASPHEMOUS_ROOT_CONFIG.YOU_GOT_A_LICENCE_FOR_THAT'],
                ]
            )
            ->expectsConfirmation('Is config paths and mapped env keys suitable ?','yes')
            ->expectsChoice('How would you like to proceed ?',$applyChoices[2],$applyChoices)
            ->expectsChoice("Append mapped env keys to which file ? (It'll append or overwrite mapped keys only, other data will be kept as it is) (Relative to base path)",$outputFileOptions[3],$outputFileOptions)
            ->expectsConfirmation("$targetFilePath doesn't exists, would you like it to be created ?",'yes')
            ->expectsOutput("File doesn't contain mapped env keys, appending to the end of the file")
            ->assertSuccessful();


        $this->assertEquals($expected,$this->getEnvFile('laravel-config-mapper.env'));


    }
}