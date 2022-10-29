<?php


namespace Skywarth\LaravelConfigMapper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PublishMappedEnvKeys extends Command
{
    protected $signature = 'laravel-config-mapper:publish-env-keys';

    protected $description = '(pending description)';//TODO add description

    public function handle()
    {

        /*$text = '<?php return ' . 'sssssssss' . ';';
        file_put_contents(base_path('testzz.php'), $text);
        dd('q');*/

        $this->info('1. Discovering config files');

        $configs=$this->getAllConfigsArray();
        $automapConfigs=$configs;


        $this->info('2. Filtering non-automap configs out');
        $this->filterOutNonAutomapConfigs($automapConfigs);
        //dump($automapConfigs);
        $results = array();
        $normalizedAutomapConfigs=$this->flatten($automapConfigs);

        $configsTableForDisplay=[];
        foreach ($normalizedAutomapConfigs as $configPath=>$value){
            $mappedEnvKey=\ConfigMapper::getMappedEnvKey($configPath);
            $normalizedAutomapConfigs[$configPath]=$mappedEnvKey;
            $configsTableForDisplay[]=[
                'config_path'=>$configPath,
                'mapped_env_key'=>$mappedEnvKey
            ];
        }

        $this->info('3. Automap configs below:');
        $this->table(
            ['Config Path','Automap Env key'],
            $configsTableForDisplay
        );


        if (!$this->confirm('Is config paths and mapped env keys suitable ?')) {
            $this->warn("You should tinker with laravel-config-mapper's config to your liking. Then run this command again.");
        }

        $this->info('4. Copy this and paste it to your env, then edit values as you wish:');
        $outputString="#AUTOMAP ENV KEYS BEGIN\n\n";
        foreach ($normalizedAutomapConfigs as $configPath=>$envKey){
            $outputString.=$envKey."=\n";
        }

        $outputString.="\n#AUTOMAP ENV KEYS END";
        $this->info('--------------COPY BELOW--------------');
        $this->line($outputString);
        $this->info('--------------COPY ABOVE--------------');
        $this->warn("Don't forget to assign values to your env keys !");
        exit(1);

    }



    private function getAllConfigsArray():array{
        $config=config();//this gives us all configs as Illuminate\Config\Repository. It includes subdirs too
        //alternative: Storage::allFiles(config_path());
        return $config->all();
    }


    //https://stackoverflow.com/a/62807978 Thanks
    function flatten($array, $prefix = '') {
        $folderDelimiter='.';//intentionally independent from ConfigMapper's config
        $return = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, $this->flatten($value, $prefix . $key . $folderDelimiter));
            } else {
                $return[$prefix . $key] = $value;
            }
        }
        return $return;
    }


    //https://stackoverflow.com/a/29612139 thanks
    private function filterOutNonAutomapConfigs(&$configs) :array{//CAREFUL, notice the ampersand. Updates in place.
        foreach ( $configs as $key => $item ) {
            is_array ( $item ) && $configs[$key] = $this->filterOutNonAutomapConfigs( $item );
            if ((!is_array($configs[$key]) && $configs[$key]!=='automap')||empty($configs[$key])){
                unset($configs[$key]);
            }

        }
        return $configs;
    }

}