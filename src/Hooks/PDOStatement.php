<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Sanprojects\Interceptor\Hooks;

class PDOStatement extends \PDOStatement
{
    public PDO $pdo;
    protected array $params = [];

    public function bindValue($param, $value, $type = \PDO::PARAM_STR)
    {
        $this->params[$param] = $value;

        return parent::bindValue(...func_get_args());
    }

    public function bindParam($column, &$variable, $type = \PDO::PARAM_STR, $length = null, $driverOptions = null)
    {
        $this->params[$column] = $variable;

        return parent::bindParam(...func_get_args());
    }

    public function execute($params = null)
    {
        $this->params = array_merge($params ?? [], $this->params);

        return PDOHook::hookFunction(
            fn() => parent::execute(...func_get_args()),
            func_get_args(),
            [$this->fullQuery()],
            'PDOStatement::execute ' . $this->getServerName()
        );
    }

    public function fetchColumn($column = 0)
    {
        return PDOHook::hookFunction(
            fn() => parent::fetchColumn(...func_get_args()),
            func_get_args(),
            [],
            'PDOStatement::fetchColumn ' . $this->getServerName() . ' ' . $this->fullQuery()
        );
    }

    public function fetch($mode = PDO::FETCH_BOTH, $cursorOrientation = PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        return PDOHook::hookFunction(
            fn() => parent::fetch(...func_get_args()),
            func_get_args(),
            [],
            'PDOStatement::fetch ' . $this->getServerName() . ' ' . $this->fullQuery()
        );
    }

    public function rowCount()
    {
        return PDOHook::hookFunction(
            fn() => parent::rowCount(...func_get_args()),
            func_get_args(),
            [],
            'PDOStatement::fetch ' . $this->getServerName() . ' ' . $this->fullQuery()
        );
    }

    public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
    {
        return PDOHook::hookFunction(
            fn() => parent::fetchAll(...func_get_args()),
            func_get_args(),
            [],
            'PDOStatement::fetch ' . $this->getServerName() . ' ' . $this->fullQuery()
        );
    }

    public function fetchObject($class_name = NULL, $ctor_args = NULL)
    {
        return PDOHook::hookFunction(
            fn() => parent::fetchObject(...func_get_args()),
            func_get_args(),
            [],
            'PDOStatement::fetch ' . $this->getServerName() . ' ' . $this->fullQuery()
        );
    }

    public function getServerName(): string
    {
        return $this->pdo->serverName ?? '';
    }

    public function fullQuery(): string
    {
        return $this->interpolateQuery($this->queryString, $this->params);
    }

    /**
     * Replaces any parameter placeholders in a query with the value of that
     * parameter. Useful for debugging. Assumes anonymous parameters from
     * $params are are in the same order as specified in $query
     *
     * @param string $query The sql query with parameter placeholders
     * @param array $params The array of substitution parameters
     * @return string The interpolated query
     */
    private function interpolateQuery($query, $params): string
    {
        $keys = [];
        $values = [];

        # build a regular expression for each parameter
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/'.$key.'/';
            } else {
                $keys[] = '/[?]/';
            }

            if (is_string($value)) {
                $values[$key] = "'" . $value . "'";
            } elseif (is_array($value)) {
                $values[$key] = "'" . implode("','", $value) . "'";
            } elseif (is_null($value)) {
                $values[$key] = 'NULL';
            } else {
                $values[$key] = $value;
            }
        }

        return preg_replace($keys, $values, $query, 1);
    }
}
