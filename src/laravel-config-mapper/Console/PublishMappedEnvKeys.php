<?php


namespace Skywarth\LaravelConfigMapper\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Skywarth\LaravelConfigMapper\Facades\ConfigMapper;
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

        $this->info('Discovering config files');

        $configs=ConfigMapper::getAllConfigsArray();
        $automapConfigs=$configs;


        $this->info('Filtering non-automap configs out');
        $automapConfigs=ConfigMapper::filterOutNonAutomapConfigs($automapConfigs);
        $normalizedAutomapConfigs=Utility::flatten($automapConfigs);

        $configsTableForDisplay=[];
        foreach ($normalizedAutomapConfigs as $configPath=>$value){
            $mappedEnvKey=ConfigMapper::getMappedEnvKey($configPath);
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
            exit (1);
        }



        $applyChoices=[
            1=>"Just output the mapped env keys, I'll copy them myself",
            2=>"Add mapped env keys to file",
            3=>"Update configs to replace 'automap' values with corresponding env keys, then add mapped keys to file",
        ];

        $choiceString = $this->choice(
            'How would you like to proceed ?',
            $applyChoices,
        );
        $choiceNumber=array_flip($applyChoices)[$choiceString];
        if($choiceNumber===1){
            $this->outputMappedEnvKeys($normalizedAutomapConfigs);
        }else if($choiceNumber===2){
            $this->appendToFileOptionIO($normalizedAutomapConfigs);
        } else if($choiceNumber===3){
            $this->updateAutomapConfigFiles($normalizedAutomapConfigs);
            $this->appendToFileOptionIO($normalizedAutomapConfigs);
        }else{
            $this->error('Select a valid option next time numb-nut !');
        }

        exit(1);

    }





    protected function updateAutomapConfigFiles(array $normalizedAutomapConfigs){
        foreach ($normalizedAutomapConfigs as $automapConfigPath=>$envKey){
            $paths=ConfigMapper::getConfigFilePathFromConfigKeyString($automapConfigPath);
            //maybe use Config::set ?
            config([$automapConfigPath=>"env($envKey,'automap')"]);//dynamically updating until next request cycle. Not really necessary
            //$temp = '<?php return ' . var_export(config($paths['config_string']), true) . ';';
            $fileContent=File::get($paths['file_path']);
            $linesArray=explode("\n", $fileContent);
            foreach ($linesArray as $lineNumber=>$line) {
                if(str_contains($line,"\"{$paths['key_in_file']}\"") || str_contains($line,"'{$paths['key_in_file']}'")){
                    $line=str_replace("\"automap\"","env('$envKey','automap')",$line);
                    $line=str_replace("'automap'","env('$envKey','automap')",$line);
                    $linesArray[$lineNumber]=$line;

                }
            }

            file_put_contents($paths['file_path'], implode($linesArray,"\n"));
        }
    }

    private function prepareEnvString(array $normalizedAutomapConfigs){
        $string='';
        foreach ($normalizedAutomapConfigs as $configPath=>$envKey){
            $string.=$envKey."=\n";
        }
        return trim($string);
    }

    private function ensureFileExists(string $filepath):bool{
        if(File::exists($filepath)===false){
            if($this->confirm("$filepath doesn't exists, would you like it to be created ?",true)){
                File::put($filepath,'');
            }else{
                return false;
            }

        }
        return true;
    }

    protected function outputMappedEnvKeys(array $normalizedAutomapConfigs){
        $this->info('4. Copy this and paste it to your env, then edit values as you wish:');
        $outputString=''.$this->getBeginIndicatorForEnv()."\n";
        $outputString.=$this->prepareEnvString($normalizedAutomapConfigs);

        $outputString.="\n".$this->getEndIndicatorForEnv();
        $this->info('--------------COPY BELOW--------------');
        $this->line($outputString);
        $this->info('--------------COPY ABOVE--------------');
        $this->warn("Don't forget to assign values to your env keys !");
    }

    protected function appendToFileOptionIO(array $normalizedAutomapConfigs){
        $outputFileOptions=[
            1=>".env",
            2=>".env.example",
            3=>"laravel-config-mapper.env",
        ];
        $fileChoiceString = $this->choice(
            "Append mapped env keys to which file ? (It'll append or overwrite mapped keys only, other data will be kept as it is) (Relative to base path)",
            $outputFileOptions,
        );
        $filePath=base_path($fileChoiceString);


        $this->addMappedEnvKeysToFile($normalizedAutomapConfigs,$filePath);
    }

    protected function addMappedEnvKeysToFile(array $normalizedAutomapConfigs,string $filepath):bool{
        if($this->ensureFileExists($filepath)===false){
            $this->error('Selected file path unavailable. Either create it yourself, or allow the command to create it. Also check file permissions if problem persists.');
            return false;
        }


        $envStringToPut=$this->prepareEnvString($normalizedAutomapConfigs);
        $envStringToPutArray=explode("\n", $envStringToPut);

        $fileContent=File::get($filepath);
        $linesArray=explode("\n", $fileContent);

        $beginLine=null;
        $endLine=null;
        $existingMappedEnvValues=collect();
        foreach ($linesArray as $lineNumber=>$line){
            if(str_contains($line,self::ENV_KEYS_BEGIN_INDICATOR)){//finding the section start
                $beginLine=$lineNumber;
            }else if(str_contains($line,self::ENV_KEYS_END_INDICATOR)){//finding the section end
                $endLine=$lineNumber;
                break;
            }

            if(!is_null($beginLine) && $lineNumber!==$beginLine){
                //determining assigned env values and storing them
                $exploded=explode('=',$line,2);
                if(count($exploded)==2 && $exploded[1]!=='' && $exploded[1]!==null){//intentionally didn't use !empty(), 0 is a valid value.
                    $existingMappedEnvValues->push([
                        'mapped_env_key'=>$exploded[0],
                        'value'=>$exploded[1],
                    ]);
                }
            }

        }

        if(is_null($beginLine) && is_null($endLine)){
            $this->info("File doesn't contain mapped env keys, appending to the end of the file");
            //means file doesn't contain mapped env keys
            //just add it to the end of the file

            $linesArray[]=$this->getBeginIndicatorForEnv();
            $linesArray=array_merge($linesArray,$envStringToPutArray);
            $linesArray[]=$this->getEndIndicatorForEnv();

        }else{
            $this->info("File already contains mapped env keys, replacing appropriately");
            //file already contains some mapped env keys
            //replace between starting and ending tag
            $lineIterator=$beginLine+1;

            while(count($envStringToPutArray)>0){
                $nextKeyToPut=array_shift($envStringToPutArray);
                $envKeyWithoutTrailingEqual=substr($nextKeyToPut,'0',-1);
                if($existingMappedEnvValues->contains('mapped_env_key','=',$envKeyWithoutTrailingEqual)){
                    //appending the existing value to the env key
                    $nextKeyToPut.=$existingMappedEnvValues->firstWhere('mapped_env_key','=',$envKeyWithoutTrailingEqual)['value'];
                }

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
        return true;

    }


}