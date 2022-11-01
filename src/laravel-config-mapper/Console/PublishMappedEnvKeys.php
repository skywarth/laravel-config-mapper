<?php


namespace Skywarth\LaravelConfigMapper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Skywarth\LaravelConfigMapper\Utility;

class PublishMappedEnvKeys extends Command
{
    protected $signature = 'laravel-config-mapper:publish-env-keys';

    protected $description = '(pending description)';//TODO add description

    private const ENV_KEYS_BEGIN_INDICATOR = '[AUTOMAP ENV KEYS BEGIN]';
    private const ENV_KEYS_END_INDICATOR = '[AUTOMAP ENV KEYS END]';

    /**
     * @return string
     */
    private function getBeginIndicatorForEnv(): string
    {
        return "#".self::ENV_KEYS_BEGIN_INDICATOR." - DON'T ALTER THIS LINE";
    }

    /**
     * @return string
     */
    private function getEndIndicatorForEnv(): string
    {
        return "#".self::ENV_KEYS_END_INDICATOR." - DON'T ALTER THIS LINE";
    }


    public function handle()
    {

        /*$text = '<?php return ' . 'sssssssss' . ';';
        file_put_contents(base_path('testzz.php'), $text);
        dd('q');*/

        $this->info('Discovering config files');

        $configs=\ConfigMapper::getAllConfigsArray();
        $automapConfigs=$configs;


        $this->info('Filtering non-automap configs out');
        $automapConfigs=\ConfigMapper::filterOutNonAutomapConfigs($automapConfigs);
        $normalizedAutomapConfigs=Utility::flatten($automapConfigs);

        $configsTableForDisplay=[];
        foreach ($normalizedAutomapConfigs as $configPath=>$value){
            $mappedEnvKey=\ConfigMapper::getMappedEnvKey($configPath);
            $normalizedAutomapConfigs[$configPath]=$mappedEnvKey;
            $configsTableForDisplay[]=[
                'config_path'=>$configPath,
                'mapped_env_key'=>$mappedEnvKey
            ];
        }

        $this->info('Automap configs below:');
        $this->table(
            ['Config Path','Automap Env key'],
            $configsTableForDisplay
        );


        if (!$this->confirm('Is config paths and mapped env keys suitable ?')) {
            $this->warn("You should tinker with laravel-config-mapper's config to your liking. Then run this command again.");
        }



        $applyChoices=[
            1=>"Just output the mapped env keys, I'll copy them myself",
            2=>"Add mapped env keys to .env.example file",
            3=>"Add mapped env keys to .env file",
            4=>"Update configs to replace 'automap' values with corresponding env keys",
        ];

        $choiceString = $this->choice(
            'How would you like to apply mapped keys ?',
            $applyChoices,
        );
        $choiceNumber=array_flip($applyChoices)[$choiceString];
        if($choiceNumber===1){
            $this->outputMappedEnvKeys($normalizedAutomapConfigs);
        }else if($choiceNumber===2){
            $envExampleFilePath=base_path('.env.example');

            $this->addMappedEnvKeysToFile($normalizedAutomapConfigs,$envExampleFilePath);
        }





        exit(1);

    }

    private function prepareEnvString(array $normalizedAutomapConfigs){
        $string='';
        foreach ($normalizedAutomapConfigs as $configPath=>$envKey){
            $string.=$envKey."=\n";
        }
        return trim($string);
    }

    private function outputMappedEnvKeys(array $normalizedAutomapConfigs){
        $this->info('4. Copy this and paste it to your env, then edit values as you wish:');
        $outputString=''.$this->getBeginIndicatorForEnv()."\n";
        $outputString.=$this->prepareEnvString($normalizedAutomapConfigs);

        $outputString.="\n".$this->getEndIndicatorForEnv();
        $this->info('--------------COPY BELOW--------------');
        $this->line($outputString);
        $this->info('--------------COPY ABOVE--------------');
        $this->warn("Don't forget to assign values to your env keys !");
    }

    private function addMappedEnvKeysToFile(array $normalizedAutomapConfigs,string $filepath){

        $envStringToPut=$this->prepareEnvString($normalizedAutomapConfigs);
        $envStringToPutArray=explode("\n", $envStringToPut);

        $fileContent=File::get($filepath);
        $linesArray=explode("\n", $fileContent);

        $beginLine=null;
        $endLine=null;
        foreach ($linesArray as $lineNumber=>$line){
            if(str_contains($line,self::ENV_KEYS_BEGIN_INDICATOR)){
                $beginLine=$lineNumber;
            }else if(str_contains($line,self::ENV_KEYS_END_INDICATOR)){
                $endLine=$lineNumber;
                break;
            }
        }


        if(is_null($beginLine) && is_null($endLine)){
            //means file doesn't contain mapped env keys
            //just add it to the end of the file

            $linesArray[]=$this->getBeginIndicatorForEnv();
            $linesArray=array_merge($linesArray,$envStringToPutArray);
            $linesArray[]=$this->getEndIndicatorForEnv();

        }else{
            //file already contains some mapped env keys
            //replace between starting and ending tag
            $lineIterator=$beginLine+1;

            //dump(['lineit'=>$lineIterator,'begin'=>$beginLine,'end'=>$endLine]);
            while(count($envStringToPutArray)>0){
                $nextKeyToPut=array_shift($envStringToPutArray);
                if($lineIterator===$endLine){
                    array_splice($linesArray, $lineIterator, 0, $nextKeyToPut);
                    $endLine++;
                }else{
                    $linesArray[$lineIterator]=$nextKeyToPut;
                }
                $lineIterator++;
            }

            while($lineIterator<$endLine){//to get rid of other lines (existing section is bigger than the new mapped env keys line number)
                unset($linesArray[$lineIterator]);
                $lineIterator++;
            }
        }





        $newContent=implode($linesArray,"\n");
        file_put_contents($filepath,$newContent);

    }


}