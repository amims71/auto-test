<?php

namespace Amims71\AutoTest\Console;


use PhpParser\Error;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\ParserFactory;

class UnitTestHelperJson{

    public $output='<?php';
    public $object;
    public $className;
    public $parsedInput;
    public $properties=[];
    public $methods=[];
    public $constructor='';
    public $constructorParams='';

    public function __construct($file){
        $input=file_get_contents($file);

        $this->parsedInput=json_decode($input);

        $this->className=$this->parsedInput->className;
        $this->properties=$this->parsedInput->properties??[];
        $statements=$this->parsedInput->methods??[];
        $this->methods=[];
        foreach ($statements as $statement){
            if ($statement->name=='__construct') $this->constructor=$statement;
            else array_push($this->methods,$statement);
        }

        if ($this->constructor){
            foreach ($this->constructor->arguments as $param){
                $this->constructorParams.='$this->'.$param->name.',';
            }
        }
        $this->constructorParams=substr($this->constructorParams, 0, -1);
    }
    public function addNameSpace($nameSpace){
        $this->output.=PHP_EOL.'namespace '.$nameSpace.';'.PHP_EOL.PHP_EOL;
    }
    public function addRequires(){
        $this->output.='require \'vendor/autoload.php\';'.PHP_EOL.'require \''.$this->parsedInput->fileLocation.'\';';
    }
    public function addUses(){
        $this->output.=PHP_EOL.PHP_EOL.'use '.$this->parsedInput->classLocation.';'.PHP_EOL.'use ReflectionClass;'.PHP_EOL.'use PHPUnit\Framework\TestCase;'.PHP_EOL.PHP_EOL;
    }
    public function addClassname(){
        $this->output.='class '.$this->className.'UMLTest extends TestCase';
    }
    public function addClassObject(){
        $this->output.='{'.PHP_EOL."\t".'protected $'.lcfirst($this->className).';';
    }
    public function addProperties(){
        foreach ($this->properties as $property){
            $this->output.=PHP_EOL."\t".'protected $'.$property->name.';';
        }
    }
    public function addSetUp(){
        $this->output.=PHP_EOL.PHP_EOL."\t".'protected function setUp(): void'.PHP_EOL."\t".'{'.PHP_EOL."\t\t".'parent::setUp();';
        foreach ($this->properties as $property){
            $this->output.=PHP_EOL."\t\t".'$this->'.$property->name.'=\'\'; //TODO set test value';
        }
    }
    public function addConstructedObject(){
        $this->output.="\n\t\t".'$this->'.lcfirst($this->className).' = new '.$this->className.'('.$this->constructorParams.');'."\n\t".'}';
    }
    public function addTearDownMethod(){
        $this->output.="\n\n\t".'protected function tearDown(): void'."\n\t".'{'."\n\t\t".'parent::tearDown();'."\n\n\t\t".'unset($this->'.lcfirst($this->className).');';
        foreach ($this->properties as $property){
            $this->output.="\n\t\t".'unset($this->'.$property->name.');';
        }
        $this->output.="\n\t".'}'."\n\n";
    }
    public function addMethods($testMethods,$method){

        $prprty='';
        $asrt='';
        if (@$method->returnType=='void'){
            $params=$method->arguments;
            $paramNames=[];
            $getProp=[];
            $getAssert=[];
            if (sizeof($params)>0) {
                foreach($params as $key=>$param){
                    $paramName='$'.$param->name;
                    array_push($paramNames,$paramName);
                    $prprty.=$paramName.'=\'\'; //TODO set test value'."\n\t\t";
                    $prop="\n\t\t".'$property'.$key.' = (new ReflectionClass('.$this->className.'::class))'."\n\t\t\t".'->getProperty(\''.$param->name.'\');'."\n\t\t".'$property'.$key.'->setAccessible(true);';
                    array_push($getProp,$prop);
                    $asrt='$this->assertSame('.$paramName.', $property'.$key.'->getValue($this->'.lcfirst($this->className).'));'."\n\t\t";
                    array_push($getAssert,$asrt);
                }
            }
            $proprty='$this->'.lcfirst($this->className).'->'.$method->name.'('.implode(',', $paramNames).');';
            $testMethods.="\t".'public function test'.ucfirst($method->name).'(): void'."\n\t".'{'."\n\t\t".$prprty."\n\t\t".$proprty."\n\t\t".implode('',$getProp)."\n\t\t".implode('',$getAssert)."\n\t".'}'."\n\n";
        } else{
            $params=$method->arguments;
            $paramNames=[];
            if (sizeof($params)>0) {
                foreach($params as $param){
                    $paramName='$'.$param->name;
                    array_push($paramNames,$paramName);
                    $prprty.=$paramName.'=\'\'; //TODO set test value'."\n\t\t";
                }
            }
            $paramNames=implode(',', $paramNames);
            $asrt='$this->assertSame($expected, $this->'.lcfirst($this->className).'->'.$method->name.'('.$paramNames.'));';
            $testMethods.="\t".'public function test'.ucfirst($method->name).'(): void'."\n\t".'{'."\n\t\t".'$expected = \'\';//TODO set test value'."\n\t\t".$prprty."\n\t\t".$asrt."\n\t".'}'."\n\n";
        }
        return $testMethods;
    }
    public function closeClass(){
        $this->output.='}';
    }
}
