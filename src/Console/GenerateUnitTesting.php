<?php

namespace Amims71\AutoTest\Console;


use Illuminate\Console\Command;

class GenerateUnitTesting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:test {type} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Unit Test';

    protected $unitTestHelper;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $file=$this->argument('file');
        $type=$this->argument('type');
        if ($type=='php'){
            $this->unitTestHelper=new UnitTestHelper($file);
            $this->unitTestHelper->addNameSpace('tests');
            $this->unitTestHelper->addRequires($file);
            $this->unitTestHelper->addUses();
            $this->unitTestHelper->addClassname();
            $this->unitTestHelper->addClassObject();
            $this->unitTestHelper->addProperties();
            $this->unitTestHelper->addSetUp();
            $this->unitTestHelper->addConstructedObject();
            $this->unitTestHelper->addTearDownMethod();
            $testMethods='';
            foreach ($this->unitTestHelper->methods as $method){
                $testMethods=$this->unitTestHelper->addMethods($testMethods,$method);
            }
            $this->unitTestHelper->output.=$testMethods;
            $this->unitTestHelper->addTestAttributes();
            $this->unitTestHelper->closeClass();
            $fileName='tests/'.$this->unitTestHelper->class->name->name.'Test.php';
            file_put_contents($fileName,$this->unitTestHelper->output);
            $this->info($this->unitTestHelper->output);
        } elseif ($type=='json'){
            $this->unitTestHelper=new UnitTestHelperJson($file);
            $this->unitTestHelper->addNameSpace('tests');
            $this->unitTestHelper->addRequires();
            $this->unitTestHelper->addUses();
            $this->unitTestHelper->addClassname();
            $this->unitTestHelper->addClassObject();
            $this->unitTestHelper->addProperties();
            $this->unitTestHelper->addSetUp();
            $this->unitTestHelper->addConstructedObject();
            $this->unitTestHelper->addTearDownMethod();
            $testMethods='';
            foreach ($this->unitTestHelper->methods as $method){
                $testMethods=$this->unitTestHelper->addMethods($testMethods,$method);
            }
            $this->unitTestHelper->output.=$testMethods;
            $this->unitTestHelper->closeClass();
            file_put_contents('tests/'.$this->unitTestHelper->className.'UMLTest.php',$this->unitTestHelper->output);
            $this->info($this->unitTestHelper->output);

        }
    }
}
