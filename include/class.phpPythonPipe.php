<?php
  /****************************************************************************
   * A class to execute python code from PHP without using temp files
   *
   * Example usage:    
   * require_once("class.phpPythonPipe.php");
   * $pythonCode  = "import sys\n";
   * $pythonCode .= "print(\"Hello\")\n";
   * $python = new phpPythonPipe();
   * $python->kernelPath = "~/anaconda3/bin/python";
   * $python->code = $pythonCode;
   * $python->exec();
   * $python->print();
   * 
   * @package phpPythonPipe
   * @author  Filipi Vianna
   * @version 0.0.1
   * $access  public
   * @see     https://github.com/filipi/phpPythonPipe
   */
class phpPythonPipe{
  public $kernelPath = '/usr/bin/python';
  public $code = '';
  public $output = '';  
  public $showSTDERR = false;
  
  public $debugLevel = 0;

  private $blackListCommands = array();  

  /**
   * https://www.owasp.org/index.php/Testing_for_Command_Injection_(OTG-INPVAL-013)
   * black list system near commands:
   */
  public function __construct(){
    $this->blackListCommands['python'][] = "eval";  
    $this->blackListCommands['python'][] = "os.system";  
    $this->blackListCommands['python'][] = "os.popen";  
    $this->blackListCommands['python'][] = "subprocess.popen";  
    $this->blackListCommands['python'][] = "subprocess.call";  
 
    $this->blackListCommands['PHP'][] = "system";  
    $this->blackListCommands['PHP'][] = "shell_exec";  
    $this->blackListCommands['PHP'][] = "exec";  
    $this->blackListCommands['PHP'][] = "proc_open";  
    $this->blackListCommands['PHP'][] = "eval";  
    $this->blackListCommands['PHP'][] = "passthru";  
    $this->blackListCommands['PHP'][] = "proc_open";  
    $this->blackListCommands['PHP'][] = "expect_open";  
    $this->blackListCommands['PHP'][] = "ssh2_exec";  
    $this->blackListCommands['PHP'][] = "popen";        
  }

  /**
   * Removes system near python commands to prevent code injection
   * @param void
   * @return void
   * @access private
   */
  private function codeInjectionCheck(){
    foreach($this->blackListCommands['python'] as $command){
      $this->code = str_replace( $command, '', $this->code);
      if ($this->debugLevel > 3){
        echo "-------------------------\n";
        echo "CODE: " . $this->code;
        echo "-------------------------\n";
      }
    }
  }  
  
  /**
   * Executes the python code and stores output in $this->output property
   * @param void
   * @return void
   * @access public
   */
  public function exec() {    
    $this->codeInjectionCheck();
    /*  
    -s     Don't add user site directory to sys.path.

    -S     Disable the import of the module site and the site-dependent
           manipulations of sys.path that it entails.
           (think to create a black list of modules...)
    */      
    //$command = "export PYTHONDUMPREFS=1 & " . $this->kernelPath . " -s -c '" . $this->code . "'";
    $command = $this->kernelPath . " -c '" . $this->code . "'  " . ($this->showSTDERR ? " 2>&1 " : "");
    $this->output = `$command`;
  }

  /**
   * Echos the output from $this->output property
   * @param void
   * @return void
   * @access public
   */
  public function print(){
    echo $this->output;
  }

  /**
   * pass all variables to PHP
   * https://stackoverflow.com/questions/192109/is-there-a-built-in-function-to-print-all-the-current-properties-and-values-of-a
   * >>> l = dir(__builtins__)
   * >>> d = __builtins__.__dict__
   * Print that dictionary however fancy you like:
   * 
   * >>> print l
   * ['ArithmeticError', 'AssertionError', 'AttributeError',...
   * or
   * 
   * >>> from pprint import pprint
   * >>> pprint(l)
   * ['ArithmeticError',
   *  'AssertionError',
   *  'AttributeError',
   *  'BaseException',
   *  'DeprecationWarning',
   * ...
   * 
   * >>> pprint(d, indent=2)
   */

}
?>
