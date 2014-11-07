#ifndef _COMMAND_PARSE_H_
#define _COMMAND_PARSE_H_

typedef int (*command_func)(char *args[], int arg_num);

struct command_t
{
        char *command;
        command_func func;
};

command_func lookup_command(char *requested_command);
int serial_read_command(char *cmd[]);

#endif
