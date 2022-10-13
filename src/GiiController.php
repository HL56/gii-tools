<?php

namespace Hl56\GiiTools;

use Yii;
use yii\console\Controller;
use yii\helpers\BaseConsole;
use Hl56\GiiTools\crud\Generator as CrudGenerator;
use Hl56\GiiTools\model\Generator as ModelGenerator;

class GiiController extends Controller
{
    /**
     * @var string 模块名
     */
    public $moduleName;

    public function actionCrud(...$tables)
    {
        //生成公共模型
        $this->generateCommonModel(...$tables);

        foreach ($tables as $table) {
            try {
                $generator = new CrudGenerator();

                //生成模型
                $this->writeModelFile($tableName = $this->formatTableName($table));

                $generator->modelClass          = $this->moduleName . "\models\\" . $tableName;
                $generator->controllerClass     = $this->moduleName . "\controllers\\" . $tableName . "Controller";
                $generator->baseControllerClass = $this->moduleName . "\controllers\\BaseController";

                $files = $generator->generate();
                if (count($files) === 0) {
                    $this->stdout(PHP_EOL . "$table 没有文件要生成.", BaseConsole::FG_RED);
                    continue;
                }

                foreach ($files as $file) {
                    if (file_exists($file->path)) continue;
                    $this->writeContentFile($file->path, $file->content);
                }

            }catch ( \Exception $e ) {
                $this->stdout(PHP_EOL . "$table 生成失败.". PHP_EOL, BaseConsole::FG_RED);
                $this->stdout(PHP_EOL . $e->getTraceAsString() . PHP_EOL, BaseConsole::FG_RED);
                $this->stdout(PHP_EOL . $e->getMessage() . PHP_EOL, BaseConsole::FG_RED);
                break;
            }
        }
    }

    public function generateCommonModel(...$tables)
    {
        foreach ($tables as $table) {
            try {
                $generator = new ModelGenerator();

                // try {
                //     //判断对应库数据表是否存在
                //     Yii::$app->db->createCommand(sprintf("desc %s.%s", GII_DB_NAME, $table))->queryOne();
                // } catch (\Exception $e) {
                //     $this->stdout($e->getMessage() . PHP_EOL, BaseConsole::FG_BLUE);
                //     exit;
                // }

                $generator->queryNs    = 'common\models';                   //命名空间
                $generator->ns         = 'common\models';                   //命名空间
                $generator->modelClass = $this->formatTableName($table);    //格式化类名
                $generator->tableName  = $table;

                $files = $generator->generate();
                if (count($files) === 0) {
                    $this->stdout(PHP_EOL . "$table 没有文件要生成.", BaseConsole::FG_RED);
                    continue;
                }

                foreach ($files as $file) {
                    if (file_exists($file->path)) continue;
                    $this->writeContentFile($file->path, $file->content);
                }
            } catch ( \Exception $e ) {
                $this->stdout(PHP_EOL . "$table 生成失败.". PHP_EOL, BaseConsole::FG_RED);
                $this->stdout(PHP_EOL . $e->getTraceAsString() . PHP_EOL, BaseConsole::FG_RED);
                $this->stdout(PHP_EOL . $e->getMessage() . PHP_EOL, BaseConsole::FG_RED);
                break;
            }
        }
    }

    /**
     * @param $tableName
     * @return string|string[]
     */
    protected function formatTableName($tableName)
    {
        $tableName = ucwords(str_replace('_', ' ', $tableName));

        return str_replace(' ', '', $tableName);
    }

    /**
     * @throws \Exception
     */
    protected function writeModelFile($tableName)
    {
        $backendModelFile = Yii::getAlias("@" . $this->moduleName . "/models/" . $tableName . ".php");
        if (!is_file($backendModelFile)) {
            $content = <<<PHP
<?php

namespace $this->moduleName\models;

class $tableName extends \common\models\\$tableName
{
    public function rules(): array
    {
        return array_merge(parent::rules(), []);
    }
}
PHP;
            $this->writeContentFile($backendModelFile, $content);
        }
    }

    /**
     * @throws \Exception
     */
    protected function writeContentFile($file, $content)
    {
        if (@file_put_contents($file, $content) === false) {
            throw new \Exception("写入文件到 $file 错误.");
        }

        $mask = @umask(0);
        @chmod($file, 0666);
        @umask($mask);
        $this->stdout("生成文件 $file 成功" . PHP_EOL, BaseConsole::FG_GREEN);
    }
}

