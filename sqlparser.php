<?php
/*************************************************************************
 * txtSQL                                                  ver. 3.0 BETA *
 *************************************************************************
 * This program is free software; you can redistribute it and/or         *
 * modify it under the terms of the GNU General Public License           *
 * as published by the Free Software Foundation; either version 2        *
 * of the License, or (at your option) any later version.                *
 *                                                                       *
 * This program is distributed in the hope that it will be useful,       *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 * GNU General Public License for more details.                          *
 *                                                                       *
 * You should have received a copy of the GNU General Public License     *
 * along with this program; if not, write to the Free Software           *
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307 *
 * USA.                                                                  *
 *-----------------------------------------------------------------------*
 *  NOTE- Tab size in this file: 8 spaces/tab                            *
 *-----------------------------------------------------------------------*
 *  ©2003 Faraz Ali, ChibiGuy Production [http://txtsql.sourceforge.net] *
 *  File: txtSQL.parser.php                                              *
 ************************************************************************/

/**
 * Tokenizes a string into components for analysis by a lexer and/or parser
 *
 * @package wordParser
 * @author Faraz Ali <FarazAli at Gmail dot com>
 * @version 3.0 BETA
 * @access public
 */
class wordParser
{
        /**
         * The current character index
         * @var bool
         * @access private
         */
        var $c         = -1;

        /**
         * The last word returned successfully
         * @access private
         */
        var $lastword = '';

        /**
         * The string that gets tokenized
         * @var string
         * @access public
         */
        var $word      = '';

        /**
         * Class constructor, sets the statement that will be broken up
         * @param string $string The string that should be tokenized
         * @return bool $success Whether the string was accepted as valid
         * @access public
         */
        function wordParser ( $string )
        {
                if ( is_string($string) )
                {
                        $this->word = $string;
                        return TRUE;
                }

                return FALSE;
        }

        /**
         * Returns the last successfully parsed word
         * @return string $lastword The last word parsed successfully
         * @access public
         */
        function getLastWord ()
        {
                return $this->lastword;
        }

        /**
         * Sets the string and resets the current character index
         * @param string $string The string that should be tokenized
         * @param bool $resetCharacterIndex Whether to reset the current character index
         * @access public
         */
        function setString ( $string, $resetCharacterIndex = TRUE )
        {
                if ( is_string($string) )
                {
                        $this->word = $string;

                        if ( $resetCharacterIndex === TRUE )
                        {
                                $this->c = -1;
                        }

                        return TRUE;
                }

                return FALSE;
        }

        /**
         * Fetches the next word that is in the string
         * @param bool $leaveQuotes Whether to leave quotes part of the string or to remove them
         * @param string $whitespace_chars Characters that are considered whitespace
         * @param bool $checkQuotes Checks whether the current word is inside a string, and if it is, then concatenate it with the next word
         * @return string $word The next word in the string
         * @access public
         */
        function getNextWord ( $leaveQuotes = FALSE, $whitespace_chars = " \t\r\n", $checkQuotes = FALSE )
        {
                /* Create some variables */
                $word       = '';
                $escaped    = FALSE;
                $inComment  = FALSE;
                $inSQuotes  = FALSE;
                $inDQuotes  = FALSE;
                $inBrackets = 0;

                /* Go through each letter in the string until there are none left or
                   there is a new word */
                while ( ( $c = $this->getNextLetter() ) !== FALSE )
                {
                        /* Inside a comment */
                        if ( $inComment === TRUE )
                        {
                                if ( $c == '*' && $this->word{ $this->c + 1 } == '/' )
                                {
                                        $inComment = FALSE;
                                        $this->c++;
                                }

                                continue;
                        }

                        /* Start of a comment */
                        elseif ( $c == '/' && $this->word{ $this->c + 1 } == '*' )
                        {
                                if ( $inSQuotes === TRUE || $inDQuotes === TRUE )
                                {
                                        $word .= '/';
                                        continue;
                                }

                                $inComment = TRUE;
                                continue;
                        }

                        /* This character is escaped */
                        if ( $escaped === TRUE )
                        {
                                $escaped = FALSE;
                                $word   .= $c;
                                continue;
                        }

                        /* The next character should be interpreted as is */
                        elseif ( $c == '\\' )
                        {
                                $escaped = TRUE;
                                continue;
                        }

			/* Start of a single quote word */
			elseif ( $c == "'" )
			{
				/* If we are not in double quotes */
				if ( $inDQuotes !== TRUE )
				{
                                        /* If we are already in single quotes, then
					this is the end of the word */
                                        if ( $inSQuotes === TRUE )
                                        {
                                                $inSQuotes = FALSE;

                                                /* Check whether to leave the quotes */
                                                if ( $leaveQuotes === TRUE || $inBrackets != 0 )
                                                {
                                                        $word .= $c;

                                                        if ( $inBrackets != 0 )
                                                        {
                                                                continue;
                                                        }
                                                }

                                                /* If the brackets index is down to 0, then this
                                                is the end of the word */
                                                if ( $inBrackets == 0 )
                                                {
                                                        if ( $checkQuotes === TRUE )
                                                        {
                                                                continue;
                                                        }

                                                        $this->c++;
                                                        return $word;
                                                }
                                        }

                                        /* Start of a single quote word */
                                        else
                                        {
                                                $inSQuotes = TRUE;
                                        }

                                        /* Check whether to leave quotes */
                                        if ( $leaveQuotes === FALSE && $inBrackets == 0 )
                                        {
                                                continue;
                                        }
                                }
                        }

                        /* Start of a double quote string */
                        elseif ( $c == '"' )
                        {
                                /* If we are not in single quotes */
                                if ( $inSQuotes !== TRUE )
                                {
                                        /* If we are already in double quotes */
                                        if ( $inDQuotes === TRUE )
                                        {
                                                /* This is the end of the double-quote word */
                                                $inDQuotes = FALSE;

                                                /* Check whether to leave the quotes */
                                                if ( $leaveQuotes === TRUE || $inBrackets != 0 )
                                                {
                                                        $word .= $c;

                                                        if ( $inBrackets != 0 )
                                                        {
                                                                continue;
                                                        }
                                                }

                                                if ( $inBrackets == 0 )
                                                {
                                                        if ( $checkQuotes === TRUE )
                                                        {
                                                                continue;
                                                        }

                                                        $this->c++;
                                                        return $word;
                                                }
                                        }

                                        /* Start of a double-quote word */
                                        else
                                        {
                                                $inDQuotes = TRUE;
                                        }

                                        /* Check whether to leave quotes */
                                        if ( $leaveQuotes === FALSE && $inBrackets == 0 )
                                        {
                                                continue;
                                        }
                                }
                        }

                        /* Start of a bracket */
                        elseif ( $c == '(' )
                        {
                                if ( $inSQuotes !== TRUE)
                                {
                                        if( $inDQuotes !== TRUE)
                                        {
                                                $inBrackets++;
                                        }
                                }
                        }

                        /* End of a bracket */
                        elseif ( $c == ')' )
                        {
                                if ( $inSQuotes !== TRUE)
                                {
                                        if( $inDQuotes !== TRUE)
                                        {
                                                $inBrackets--;
                                        }
                                }
                        }

                        /* This character is in a quotation ( single or double ) */
                        elseif ( $inSQuotes === TRUE || $inDQuotes === TRUE || $inBrackets != 0 )
                        {
                                $word .= $c;
                                continue;
                        }

                        /* End of an SQL statement */
                        elseif ( $c == ';' )
                        {
                                $this->c--;
                                return $word;
                        }

                        /* Eliminate whitespace characters */
                        else
                        {
                                if ( strpos($whitespace_chars, $c) !== FALSE )
                                {
                                        if ( trim($word, $whitespace_chars) == "" )
                                        {
                                                continue;
                                        }
                                        else
                                        {
                                                break;
                                        }
                                }
                        }

                        /* Append the current character to the word */
                        $word .= $c;
                }

                /* Add a NULL byte to the end of the word */
                if ( $this->c < strlen($this->word) )
                {
                        if ( $this->word{$this->c - 1} != NULL )
                        {
                                $word .= NULL;
                        }
                }

                /* Return the word */
                return $this->lastword = ( $word == '0' ? '00' : $word );
        }

        /**
        * Fetches the next character in the string
        * @return string $c The next character
        * @access public
        */
        function getNextLetter ()
        {
                /* Increment the character index */
                $this->c++;

                /* If there is another letter, then return it */
                if ( $this->c < strlen($this->word) )
                {
                        return $this->word{$this->c};
                }

                return FALSE;
        }

        /**
        * Issues a syntax error and gives part of string where error occurrs
        * @param mixed $arguments The arguments are set to boolean false to indicate that error has occurred
        * @access private
        */
        function throwSyntaxError ( &$arguments )
        {
                $arguments = FALSE;
                $error     = substr($this->word, $this->c - 10, $this->c + 10);
                txtSQL::_error(E_USER_NOTICE, "Syntax error near `$error`");

                return TRUE;
        }
}

/**
 * Parses an SQL Query using the wordParser and returns the arguments back to
 * the txtSQL core
 *
 * @package wordParser::sqlParser
 * @author Faraz Ali <FarazAli at Gmail dot com>
 * @version 3.0 BETA
 * @access private
 */
class sqlParser extends wordParser
{
        /**
         * Whitespace characters that should be ignored
         * @var string
         * @access private
         */
        var $whitespace = " \t\n\r\x0B";

        /**
         * Class constructor, sets the statement that will be broken up
         * @param string $statement The SQL query that should be parsed
         * @return bool $success Whether the string was accepted as valid
         * @access public
         */
        public function sqlParser ( $statement )
        {
                if ( !$this->setString($statement) )
                {
                        return FALSE;
                }

                return TRUE;
        }

        /**
         * Does the actual parsing of the SQL string
         * @return $arguments The arguments as a result of the parsing
         * @access public
         */
        public function parse ()
        {
                /* Get the action */
                $action     = $this->getNextWord(TRUE);
                $arguments  = array();
                $whitespace = " \t\n\r\x0B";

                /* Parse the right query */
                switch ( strtolower($action) )
                {
                        case 'select' :
                        {
                                $this->parseSelect($arguments);

                                break;
                        }

                        case 'insert' :
                        {
                                $this->parseInsert($arguments);

                                break;
                        }

                        case 'show' :
                        {
                                $this->parseShow($arguments);

                                break;
                        }

                        case 'create' :
                        {
                                $this->parseCreate($arguments);

                                break;
                        }

                        case 'drop' :
                        {
                                $this->parseDrop($arguments);

                                break;
                        }

                        case 'describe' :
                        {
                                $this->parseDescribe($arguments);

                                break;
                        }

                        case 'delete' :
                        {
                                $this->parseDelete($arguments);

                                break;
                        }

                        case 'update' :
                        {
                                $this->parseUpdate($arguments);

                                break;
                        }

                        case 'lock' :
                        {
                                $this->parseLock($arguments);

                                break;
                        }

                        case 'unlock' :
                        {
                                $this->parseUnlock($arguments);

                                break;
                        }

                        case 'is' :
                        {
                                $this->parseIsLocked($arguments);

                                break;
                        }

                        case 'use' :
                        {
                                $this->parseUse($arguments);

                                break;
                        }

                        case 'grant' :
                        {
                                $this->parseGrant($arguments);

                                break;
                        }

                        default :
                        {
                                /* Syntax error */
                                if ( $action == '' )
                                {
                                        $this->throwSyntaxError($arguments);
                                }

                                /* Invalid action */
                                else
                                {
                                        return txtSQL::_error(E_USER_ERROR, 'Action not supported: `' . $action . '`');
                                }
                        }
                }

                /* Return the arguments */
                if ( $arguments !== FALSE )
                {
                        return $arguments;
                }

                return FALSE;
        }

        /**
         * Parses a FROM clause
         * @param mixed $arguments The arguments thus far in the parsing
         * @access private
         */
        function parseFrom ( &$arguments )
        {
                /* Grab the table name */
                $arguments['table'] = $this->getNextWord();

                /* Filter out the table and database name if the database name exists */
                if ( strpos($arguments['table'], '.') )
                {
                        list($arguments['db'], $arguments['table']) = explode('.', $arguments['table']);
                }

                return TRUE;
        }

        /**
         * Parses a WHERE clause
         * @param mixed $arguments The arguments this far in the parsing
         * @access private
         */
        function parseWhere ( &$arguments )
        {
                /* Initiate some variables */
                $clause = '';
                $Where  = array();

                /* Go through each word of the query until we need to stop */
                while ( $word = $this->getNextWord(TRUE) )
                {

                        if ( strtolower(substr($word, 0, 3)) == 'set' )
                        {
                                $this->c -= strlen($word) + 1;
                                break;
                        }

                        switch ( strtolower($word) )
                        {
                                /* Back up if we find these keywords */
                                case 'from' :
                                {
                                        $this->c -= 5;

                                        break 2;
                                }

                                case 'limit' :
                                {
                                        $this->c -= 6;

                                        break 2;
                                }

                                case 'orderby' :
                                {
                                        $this->c -= 8;

                                        break 2;
                                }

                                /* Look for logical operators */
                                case 'and' :
                                case 'or' :
                                case 'xor' :
                                {
                                        $Where[] = $clause;
                                        $Where[] = $word;
                                        $clause  = '';

                                        break;
                                }

                                /* Append the current value to the clause */
                                default :
                                {
                                        $this->c -= strlen($word) + 1;
                                        $clause  .= $this->getNextWord(TRUE) . ' ';
                                }
                        }
                }

                /* Add the the last element onto the $Where array */
                $Where[]            = substr($clause, 0, strlen($clause) - 1);
                $arguments['where'] = $Where;

                foreach ( $arguments['where'] as $key => $value )
                {
                        if ( $key <= count($arguments['where']) - 1 )
                        {
                                $arguments['where'][$key] = rtrim($value);
                        }
                }

                return TRUE;
        }

        /**
         * Parses an ORDERBY clause
         * @param mixed $arguments The arguments this far in the parsing
         * @access private
         */
        function parseOrderBy ( &$arguments )
        {
                /* Inititate some variables */
                $orderby = array();

                /* Get all of the column */
                while ( $word = $this->getNextWord(TRUE, " \t\r\n,") )
                {
                        /* First check if they are keywords */
                        switch ( strtolower($word) )
                        {
                                /* Back up if we find these keywords */
                                case 'from' :
                                {
                                        $this->c -= 5;

                                        break 2;
                                }

                                case 'limit' :
                                {
                                        $this->c -= 6;

                                        break 2;
                                }

                                case 'orderby' :
                                {
                                        $this->c -= 8;

                                        break 2;
                                }

                                /* Remove the quotes from the column name and grab the sort direction */
                                default :
                                {
                                        $parser           = new WordParser($word);
                                        $column           = $parser->getNextWord(FALSE, " \t\r\n,");
                                        $orderby[$column] = $this->getNextWord(FALSE, " \t\r\n,");
                                }
                        }
                }

                $arguments['orderby'] = $orderby;

                return TRUE;
        }

        /**
         * Parses a LIMIT clause
         * @param mixed $arguments The arguments this far in the parsing
         * @access private
         */
        function parseLimit ( &$arguments )
        {
                /* Get the starting offset, and the stopping offset */
                $limit              = array();
                $limit[]            = $this->getNextWord(FALSE, " \t\r\n,");
                $final              = $this->getNextWord(TRUE, " \t\r\n,");

                if ( $final != "" )
                {
                        if ( $final == "''" )
                        {
                                $final = '';
                        }

                        $limit[] = $final;
                }

                $arguments['limit'] = ( count($limit) == 1 ) ? array(0, $limit[0]) : $limit;

                return TRUE;
        }

        /**
         * Parses a set of values which are used in INSERT and UPDATE queries
         * @param mixed $arguments The arguments thus far in the parsing
         * @access private
         */
        function getValueSet ( &$arguments )
        {
                /* Get all the values */
                $this->c--;
                $word = $this->getNextWord(TRUE);

                switch ( TRUE )
                {
                        /* statement is in the form "(col, col, ...) VALUES ( value, value, ... )" */
                        case ( substr($word, 0, 1) == '(' ) :
                        {
                                /* Create a parser for the column names */
                                $word          = substr( $word, 1, strlen($word) - 2);
                                $column_parser = new wordParser($word);

                                /* get column names */
                                while ( $column = $column_parser->getNextWord(FALSE, $this->whitespace . ",") )
                                {
                                        $columns[] = $column;
                                }

                                /* Get values */
                                if ( substr($word = strtolower($this->getNextWord(TRUE)), 0, 6) == 'values' )
                                {
                                        while ( $word = $this->getNextWord(TRUE, $this->whitespace . ",") )
                                        {
                                                /* Create a new parser for the values */
                                                $values        = Array();
                                                $word          = substr($word, 1, strlen($word) - 2);
                                                $word          = str_replace('\\', '\\\\', $word);
                                                $values_parser = new wordParser($word);

                                                while ( $value = $values_parser->getNextWord(TRUE, $this->whitespace . ",") )
                                                {
                                                	$values[] = $value;
                                                }

						foreach ( $columns as $key => $value )
						{
							$arguments['values'][$value] = isset($values[$key]) ? $values[$key] : 0;
						}

						/* Return whatever results we got back */
						return $arguments;
                                        }
                                }
                                else
                                {
                                        $this->throwSyntaxError($arguments);
                                }

                                break;
                        }

                        /* Statement is in the form "SET [col = value...] */
                        case ( strtolower(substr($word, 0, 3)) == 'set' ) :
                        {
                                if ( strtolower($word) == 'set' )
                                {
                                        $word = $this->getNextWord(TRUE, $this->whitespace);
                                }

                                /* Seperate all of the column/value pairs */
                                $word       = substr($word, strpos($word, '(') + 1);
                                $word       = substr($word, 0, strrpos($word, ')'));
                                $word       = str_replace('\\', '\\\\', $word);
                                $set_parser = new wordParser($word);

                                while ( $column = $set_parser->getNextWord(TRUE, $this->whitespace . ",=") )
                                {
                                        $value                        = $set_parser->getNextWord(TRUE, $this->whitespace . ",=");
                                        $arguments['values'][$column] = $value;
                                }

                                break;
                        }

                        /* Syntax error */
                        default :
                        {
                                $this->throwSyntaxError($arguments);

                                break;
                        }
                }

        }

        /**
         * Parses a SELECT query and returns the proper arguments
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseSelect ( &$arguments )
        {
                /* Initialize some variables */
                $arguments = array('action' => 'select');

                /* Look for the main keywords */
                while ( $word = $this->getNextWord(TRUE) )
                {
                        switch ( strtolower($word) )
                        {
                                case 'distinct' :
                                {
                                        $arguments['distinct']= $this->getNextWord(TRUE);

                                        break;
                                }

                                case 'from' :
                                {
                                        $this->parseFrom($arguments);

                                        break;
                                }

                                case 'where' :
                                {
                                        $this->parseWhere($arguments);

                                        break;
                                }

                                case 'orderby' :
                                {
                                        $this->parseOrderBy($arguments);

                                        break;
                                }

                                case 'limit' :
                                {
                                        $this->parseLimit($arguments);

                                        break;
                                }

                                default :
                                {
                                        if ( empty($arguments['table']) )
                                        {
                                                $this->c              -= strlen($word) + 1;
                                                $column                = $this->getNextWord(TRUE, $this->whitespace . ",");
                                                $arguments['select'][] = $column;

                                                if ( strtolower($nextword = $this->getNextWord(TRUE, $this->whitespace . ',')) == 'as' )
                                                {
                                                        $arguments['aliases'][$column] = $this->getNextWord(TRUE, $this->whitespace . ',');
                                                        break;
                                                }

                                                $this->c -= strlen($nextword) + 1;
                                        }
                                }
                        }
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses an INSERT query and returns the proper arguments
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseInsert ( &$arguments )
        {
                /* Look for a syntax error */
                if ( strtolower($this->getNextWord(TRUE)) != 'into' )
                {
                        $this->throwSyntaxError($arguments);
                        return FALSE;
                }

                /* Get the table name */
                $arguments['action'] = 'insert';
                $arguments['table']  = $this->getNextWord();

                /* Syntax Error */
                if ( $arguments['table'] == '' )
                {
                        $this->throwSyntaxError($arguments);
                        return FALSE;
                }

                elseif ( strpos($arguments['table'], '.') )
                {
                        /* Check for a valid table and database name */
                        $tableDB = explode('.', $arguments['table']);

                        list($arguments['db'], $arguments['table']) = $tableDB;
                }

                /* Get the values */
                $this->getValueSet($arguments);

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses an SHOW {DATABASES|TABLES|USERS} query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseShow ( &$arguments )
        {
                /* Get the next word so we know what to search for */
                $show = $this->getNextWord(TRUE);
                $arguments = array();

                switch ( strtolower($show) )
                {
                        /* Show databases */
                        case 'databases' :
                        {
                                $arguments['action'] = 'show databases';

                                break;
                        }

                        /* Show all users */
                        case 'users' :
                        {
                                $arguments['action'] = 'show users';

                                break;
                        }

                        /* Show tables [ in a database ] */
                        case 'tables' :
                        {
                                $arguments['action'] = 'show tables';

                                /* See if we have to look in a certain database */
                                if ( ( $word = $this->getNextWord(TRUE) ) != '' )
                                {
                                        /* Grab the database name */
                                        if ( strtolower($word) == 'in' )
                                        {
                                                $arguments['db'] = $this->getNextWord(FALSE);
                                        }

                                        /* Syntax error here */
                                        else
                                        {
                                                $this->throwSyntaxError($arguments);
                                                return FALSE;
                                        }
                                }

                                break;
                        }

                        /* Something went wrong */
                        default :
                        {
                                /* Syntax Error or incorrect action */
                                if ( $drop == '' )
                                {
                                        $this->throwSyntaxError($arguments);
                                }
                                else
                                {
                                        txtSQL::_error(E_USER_NOTICE, 'Action not supported: `DROP ' . $drop . '`');
                                }

                                return FALSE;
                        }
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses a DROP {DATABASE|TABLE} query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseDrop ( &$arguments )
        {
                /* Get the next word to find out whether to drop a table or a database */
                $drop = $this->getNextWord(TRUE);

                switch ( strtolower($drop) )
                {
                        /* Drop a database */
                        case 'database' :
                        {
                                /* Grab database name */
                                $arguments['action'] = 'drop database';
                                $arguments['db']     = $this->getNextWord();

                                break;
                        }

                        /* Drop a table */
                        case 'table' :
                        {
                                /* Get table name and possible database name */
                                $arguments['action'] = 'drop table';
                                $arguments['table']  = $this->getNextWord();

                                if ( strpos($arguments['table'], '.') )
                                {
                                        $tableDB = explode('.', $arguments['table']);

                                        list($arguments['db'], $arguments['table']) = $tableDB;
                                }

                                /* Check for a valid table */
                                if ( $arguments['table'] == '' )
                                {
                                        $this->throwSyntaxError($arguments);
                                        return FALSE;
                                }

                                break;
                        }

                        /* Something went wrong */
                        default:
                        {
                                /* Syntax Error or incorrect action */
                                if ( $drop == '' )
                                {
                                        $this->throwSyntaxError($arguments);
                                }
                                else
                                {
                                        txtSQL::_error(E_USER_NOTICE, 'Action not supported: `DROP ' . $drop . '`');
                                }

                                return FALSE;
                        }
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses a DESCRIBE query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseDescribe ( &$arguments )
        {
                /* Get the table name */
                $arguments['action'] = 'describe';
                $arguments['table']  = $this->getNextWord();

                /* Syntax error */
                if ( $arguments['table'] == '' )
                {
                        $this->throwSyntaxError($arguments);
                        return FALSE;
                }
                elseif ( strpos($arguments['table'], '.') )
                {
                        /* Check for a database name */
                        $tableDB = explode('.', $arguments['table']);

                        list($arguments['db'], $arguments['table']) = $tableDB;
                }


                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses a DELETE query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseDelete ( &$arguments )
        {
                /* Set the action*/
                $arguments['action'] = 'delete';

                /* Look for the main keywords */
                while ( $word = $this->getNextWord(TRUE) )
                {
                        switch ( strtolower($word) )
                        {
                                case 'from' :
                                {
                                        $this->parseFrom($arguments);

                                        break;
                                }

                                case 'where' :
                                {
                                        $this->parseWhere($arguments);

                                        break;
                                }

                                case 'orderby' :
                                {
                                        $this->parseOrderby($arguments);

                                        break;
                                }

                                case 'limit' :
                                {
                                        $this->parseLimit($arguments);

                                        break;
                                }
                        }
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses a CREATE {TABLE|DATABASE} query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseCreate ( &$arguments )
        {
                /* Grab the next keyword */
                $create = $this->getNextWord(TRUE);

                /* Match the keyword */
                switch ( strtolower($create) )
                {
                        /* Create a database */
                        case 'database' :
                        {
                                $arguments['action'] = 'create database';
                                $arguments['db']     = $this->getNextWord();

                                break;
                        }

                        /* Create a table */
                        case 'table' :
                        {
                                $arguments['action'] = 'create table';
                                $arguments['table']  = $this ->getNextWord();

                                /* Check whether there is a database specified, and extract it */
                                if ( empty($arguments['table']) )
                                {
                                        $this->throwSyntaxError($arguments);
                                        return FALSE;
                                }
                                if ( strpos($arguments['table'], '.') )
                                {
                                        $tableDB = explode('.', $arguments['table']);

                                        list($arguments['db'], $arguments['table']) = $tableDB;
                                }

                                /* Grab each column name and their respective properties */
                                if ( substr( $word = $this->getNextWord(), 0, 1) == '(' )
                                {
                                        /* Create a new parser for the columns */
                                        $word   = substr($word, 1, strlen($word) - 2);
                                        $parser = new wordParser($word);

                                        /* Grab the properties of each column */
                                        while ( $column = $parser->getNextWord(TRUE, ",", TRUE) )
                                        {
                                                /* Create a new parser for the properties */
                                                $column_parser = new wordParser($column);
                                                $name          = $column_parser->getNextWord(FALSE);

                                                if ( !empty($name) && !isset($arguments['columns'][$name]) )
                                                {
                                                        $arguments['columns'][$name] = Array();
                                                }

                                                /* Go through each of the properties */
                                                while ( $column_properties = $column_parser->getNextWord() )
                                                {
                                                        $lowercase_properties = strtolower($column_properties);

                                                        /* Validate the properties */
                                                        switch ( TRUE )
                                                        {
                                                                /* Auto_increment */
                                                                case ( $lowercase_properties == 'auto_increment' ) :
                                                                {
                                                                        $arguments['columns'][$name]['auto_increment'] = 1;

                                                                        break;
                                                                }

                                                                /* Primary key */
                                                                case ( $lowercase_properties == 'primary' ) :
                                                                {
                                                                        if ( strtolower($column_parser->getNextWord()) != 'key' )
                                                                        {
                                                                                $column_parser->c -= 7;
                                                                                $column_parser->throwSyntaxError($arguments);

                                                                                return FALSE;
                                                                        }

                                                                        $arguments['columns'][$name]['primary'] = 1;

                                                                        break;
                                                                }

                                                                /* Permanent column */
                                                                case ( $lowercase_properties == 'permanent' ) :
                                                                {
                                                                        $arguments['columns'][$name]['permanent'] = 1;

                                                                        break;
                                                                }

                                                                /* Default value for this column */
                                                                case ( $lowercase_properties == 'default' ) :
                                                                {
                                                                        $arguments['columns'][$name]['default'] = $column_parser->getNextWord(FALSE);

                                                                        break;
                                                                }

                                                                /* Type: String */
                                                                case ( substr($lowercase_properties, 0, 6) == 'string' ) :
                                                                {
                                                                        $arguments['columns'][$name]['type'] = 'string';

                                                                        /* Look for a maximum value */
                                                                        if ( substr($lowercase_properties, 6, 1) == '(' && substr($lowercase_properties, -1, 1) == ')' )
                                                                        {
                                                                                $end = strlen($lowercase_properties) - 8;
                                                                                $max = substr($lowercase_properties, 7, $end);

                                                                                $arguments['columns'][$name]['max'] = empty($max) ? 0 : $max;
                                                                        }

                                                                        /* Syntax Error */
                                                                        elseif ( strlen($lowercase_properties) > 6 )
                                                                        {
                                                                                $column_parser->throwSyntaxError($arguments);

                                                                                return FALSE;
                                                                        }

                                                                        break;
                                                                }

                                                                /* Type: text */
                                                                case ( substr($lowercase_properties, 0, 4) == 'text' ) :
                                                                {
                                                                        $arguments['columns'][$name]['type'] = 'text';

                                                                        /* Look for a maximum value */
                                                                        if ( substr($lowercase_properties, 4, 1) == '(' && substr($lowercase_properties, -1, 1) == ')' )
                                                                        {
                                                                                $end = strlen($lowercase_properties) - 6;
                                                                                $max = substr($lowercase_properties, 5, $end);

                                                                                $arguments['columns'][$name]['max'] = empty($max) ? 0 : $max;
                                                                        }

                                                                        /* Syntax error */
                                                                        elseif ( strlen($lowercase_properties) > 4 )
                                                                        {
                                                                                $column_parser->throwSyntaxError($arguments);

                                                                                return FALSE;
                                                                        }

                                                                        break;
                                                                }

                                                                /* Type: int */
                                                                case ( substr($lowercase_properties, 0, 3) == 'int' ) :
                                                                {
                                                                        $arguments['columns'][$name]['type'] = 'int';

                                                                        /* Look for a maximum value */
                                                                        if ( substr($lowercase_properties, 3, 1) == '(' && substr($lowercase_properties, -1, 1) == ')' )
                                                                        {
                                                                                $end = strlen($lowercase_properties) - 5;
                                                                                $max = substr($lowercase_properties, 4, $end);

                                                                                $arguments['columns'][$name]['max'] = empty($max) ? 0 : $max;
                                                                        }

                                                                        /* Syntax Error */
                                                                        elseif ( strlen($lowercase_properties) > 3 )
                                                                        {
                                                                                $column_parser->throwSyntaxError($arguments);

                                                                                return FALSE;
                                                                        }

                                                                        break;
                                                                }

                                                                /* Type: bool */
                                                                case ( $lowercase_properties == 'bool' ) :
                                                                {
                                                                        $arguments['columns'][$name]['type'] = 'bool';

                                                                        break;
                                                                }

                                                                /* Type: date */
                                                                case ( $lowercase_properties == 'date' ) :
                                                                {
                                                                        $arguments['columns'][$name]['type'] = 'date';

                                                                        break;
                                                                }

                                                                /* Type: enum */
                                                                case ( substr($lowercase_properties, 0, 4) == 'enum' ) :
                                                                {
                                                                        $arguments['columns'][$name]['type'] = 'enum';

                                                                        /* Grab the enum values specified for this column */
                                                                        if ( substr($lowercase_properties, 4, 1) == '(' && substr($lowercase_properties, -1, 1) == ')' )
                                                                        {
                                                                                $end            = strlen($lowercase_properties) - 6;
                                                                                $enum_statement = substr($lowercase_properties, 5, $end);
                                                                                $enum_parser    = new wordParser($enum_statement);

                                                                                while ( $enumerations = $enum_parser->getNextWord(FALSE, $this->whitespace . ",") )
                                                                                {
                                                                                        $arguments['columns'][$name]['enum_val'][] = $enumerations;
                                                                                }

                                                                                unset($arguments['columns'][$name]['default']);
                                                                        }

                                                                        /* Syntax Error */
                                                                        else
                                                                        {
                                                                                $column_parser->throwSyntaxError($arguments);

                                                                                return FALSE;
                                                                        }

                                                                        break;
                                                                }

                                                                /* Syntax Error */
                                                                default :
                                                                {
                                                                        $this->throwSyntaxError($arguments);

                                                                        return FALSE;
                                                                }
                                                        }
                                                }
                                        }
                                }

                                break;
                        }

                        /* Something went wrong */
                        default :
                        {
                                /* Syntax Error or incorrect action */
                                if ( $create == '' )
                                {
                                        $this->throwSyntaxError($arguments);
                                }
                                else
                                {
                                        txtSQL::_error(E_USER_NOTICE, 'Action not supported: `CREATE ' . $create . '`');
                                }

                                return FALSE;
                        }
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses an UPDATE query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseUpdate ( &$arguments )
        {
                $arguments['action'] = 'update';
                $arguments['table']  = $this->getNextWord();

                if ( strpos($arguments['table'], '.') )
                {
                        list($arguments['db'], $arguments['table']) = explode('.', $arguments['table']);
                }

                /* Look for some main keywords */
                while ( $word = $this->getNextWord() )
                {
                        switch ( TRUE )
                        {
                                /* Grab the value-set */
                                case ( strtolower(substr($word, 0, 3)) == 'set' ) :
                                {
                                        $this->c -= strlen($word) + 1;
                                        $this->getValueSet($arguments);

                                        break;
                                }

                                /* Parse the where clause */
                                case ( strtolower($word) == 'where' ) :
                                {
                                        $this->parseWhere($arguments);
                                        $this->c--;

                                        break;
                                }

                                /* Parse the orderby clause */
                                case ( strtolower($word) == 'orderby' ) :
                                {
                                        $this->parseOrderBy($arguments);

                                        break;
                                }

                                /* Parse the limit clause */
                                case ( strtolower($word) == 'limit' ) :
                                {
                                        $this->parseLimit($arguments);

                                        break;
                                }
                        }
                }

                /* Return whatever results we got back */
                return $arguments;
        }

        /**
         * Parses a GRANT PERMISSIONS query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseGrant ( &$arguments )
        {
                /* Set our action */
                $arguments['action'] = 'grant permissions';

                if ( ( strtolower($this->getNextWord()) != 'permissions' ) || ( strtolower($this->getNextWord()) != 'to' ) )
                {
                        $this->throwSyntaxError($arguments);
                        return FALSE;
                }

                /* Grab the username, and the action */
                $user   = $this->getNextWord();
                $action = strtolower($this->getNextWord());
                $set    = $this->getNextWord(TRUE, $this->whitespace);

                if ( substr(strtolower($set), 0, 3) != 'set' )
                {
                        $this->throwSyntaxError($arguments);
                        return FALSE;
                }

                /* If there is a break between the letters 'SET' and the actual set, then fix it */
                elseif ( strtolower($set) == 'set' )
                {
                        $set = $this->getNextWord();
                }

                /* Grab the passwords in the set */
                $passwords       = substr($set, strpos($set, '(') + 1);
                $passwords       = substr($passwords, 0, strrpos($passwords, ')'));
                $password_parser = new wordParser($passwords);

                /* Check for a valid action, and create the php code for it */
                switch ( $action )
                {
                        /* Add a user */
                        case 'add' :

                        /* Drop a user */
                        case 'drop' :
                        {
                                $pass             = $password_parser->getNextWord();
                                $arguments['php'] = '$this->grant_permissions("' . $action . '", "' . $user . '", "' . $pass . '");';

                                break;
                        }

                        /* Edit a user */
                        case 'edit' :
                        {
                                $pass             = $password_parser->getNextWord(FALSE, $this->whitespace . ",");
                                $newpass          = $password_parser->getNextWord(FALSE, $this->whitespace . ",");
                                $arguments['php'] = '$this->grant_permissions("' . $action . '", "' . $user . '", "' . $pass . '", "' . $newpass . '");';

                                break;
                        }

                        /* Syntax Error */
                        default:
                        {
                                $this->throwSyntaxError($arguments);

                                return FALSE;
                        }
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses a LOCK [database] query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseLock ( &$arguments )
        {
                /* Set our action */
                $arguments['action'] = 'lock db';

                /* Look for a valid db name */
                if ( ( $arguments['db'] = $this->getNextWord() ) === FALSE || empty($arguments['db']) )
                {
                        $this->throwSyntaxError($arguments);
                        return FALSE;
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses an UNLOCK [database] query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseUnlock ( &$arguments )
        {
                /* Set our action */
                $arguments['action'] = 'unlock db';

                /* Look for a valid db name */
                if ( ( $arguments['db'] = $this->getNextWord() ) === FALSE || empty($arguments['db']) )
                {
                        $this->throwSyntaxError($arguments);
                        return FALSE;
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses an IS LOCKED [database] query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseIsLocked ( &$arguments )
        {
                /* Set our action */
                $arguments['action'] = 'is locked';

                if ( strtolower($this->getNextWord()) != 'locked' )
                {
                        $this->throwSyntaxError($arguments);

                        return FALSE;
                }

                /* Look for a valid db name */
                if ( ( ( $arguments['db'] = $this->getNextWord() ) === FALSE ) || empty($arguments['db']) )
                {
                        $this->throwSyntaxError($arguments);

                        return FALSE;
                }

                /* Return our arguments */
                return $arguments;
        }

        /**
         * Parses a USE [database] query
         * @param mixed $arguments The arguments this far in the parsing
         * @return mixed $arguments The new arguments after the changes have been applied
         * @access private
         */
        function parseUse ( &$arguments )
        {
                /* Set our action */
                $arguments['action'] = 'use database';

                /* Look for a valid db name */
                if ( ( $arguments['db'] = $this->getNextWord() ) === FALSE || empty($arguments['db']) )
                {
                        $this->throwSyntaxError($arguments);

                        return FALSE;
                }

                /* Return our arguments */
                return $arguments;
        }

}

?>
