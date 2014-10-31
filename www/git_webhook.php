<?
try
{
	$payload = json_decode($_REQUEST['payload']);
}
catch(Exception $e)
{
	exit(0);
}

file_put_contents('../logs/github.txt', print_r($payload, TRUE), FILE_APPEND);

if($payload->ref === 'refs/heads/master')
{
	file_put_contents('../logs/github.txt', "RUNNING GIT PULL FROM " . getcwd() . "!\n", FILE_APPEND);
	file_put_contents('../logs/github.txt', shell_exec("cd " . getcwd() . " && ./update.sh"), FILE_APPEND);
}
