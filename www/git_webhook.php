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
	$command = "cd ../ && git pull 2>&1 > /tmp/log";
	file_put_contents('../logs/github.txt', shell_exec($command), FILE_APPEND);
}
