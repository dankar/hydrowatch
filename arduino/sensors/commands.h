#ifndef _COMMANDS_H_
#define _COMMANDS_H_

extern int num_commands;
extern command_t commands[];

void init_commands();

// Prints report from sensors on serial
int get_report(char *args[], int arg_num);


#endif
