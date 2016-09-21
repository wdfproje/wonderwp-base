<?php

namespace WonderWp\Plugin\Generator;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Schema\MySqlSchemaManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\SchemaTool;
use WonderWp\Entity\AbstractEntity;
use WonderWp\DI\Container;
use WonderWp\Forms\Fields\HiddenField;
use WonderWp\Forms\Fields\InputField;
use WonderWp\Forms\Fields\TextAreaField;
use WonderWp\Forms\Form;
use WonderWp\Plugin\Faq\FaqEntity;
use WonderWp\Plugin\Translator\LangEntity;
use WonderWp\Plugin\Translator\LocoAdmin;

class PluginGenerator
{

    protected $_data;
    protected $_metaDatas;

    protected $_container;
    protected $_manager;
    /** @var  \WP_Filesystem_Direct */
    protected $_fileSystem;
    protected $_folders;

    public function __construct()
    {
        $this->_container = Container::getInstance();
        $this->_fileSystem = $this->_container->offsetGet('wwp.fileSystem');
        $this->_manager = $this->_container->offsetGet('wonderwp_generator.Manager');
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->_data['table'] = $table;
    }

    public function getPluginForm($data)
    {
        $container = Container::getInstance();

        /** @var Form $form */
        $form = $container->offsetGet('wwp.forms.form');

        //Table
        $f = new HiddenField('table', !empty($data['table']) ? $data['table'] : null);
        $form->addField($f);

        //Plugin Name
        $f = new InputField('name', !empty($data['name']) ? $data['name'] : null, ['label' => 'Nom du plugin']);
        $form->addField($f);

        //Plugin Description
        $f = new TextAreaField('desc', !empty($data['desc']) ? $data['desc'] : null, ['label' => 'Description']);
        $form->addField($f);

        //Namespace
        $f = new InputField('namespace', !empty($data['namespace']) ? $data['namespace'] : null, ['label' => 'Namespace']);
        $form->addField($f);

        //Entity Name
        $f = new InputField('entityname', !empty($data['entityname']) ? $data['entityname'] : null, ['label' => 'Nom de l\'Entité']);
        $form->addField($f);

        return $form;
    }

    public function generate()
    {
        $this->_getClassMetaDatas()
            ->_createFolders()
            ->_generateIndexFile()
            ->_generateBootsrtapFile()
            ->_generateManager()
            ->_generateActivator()
            ->_generateDeActivator()
            ->_generateAdminController()
            ->_generateEntity()
            ->_generateForm()
            ->_generateListTable()
            ->_generateHookService()
            ->_generateLanguages()
            ->_generateIcon();
    }

    protected function _getClassMetaDatas()
    {
        /** @var EntityManager $em */
        $em = $this->_container->offsetGet('entityManager');

        $schemaManager = $em->getConnection()->getSchemaManager();
        $driver = new DatabaseDriver($schemaManager);

        $this->_data['className'] = $this->_data['entityname'];
        $className = $this->_data['namespace'] . '\\' . $this->_data['className'] . 'Entity';
        $driver->setClassNameForTable($this->_data['table'], $className);


        $this->_metaDatas = new ClassMetadata($className);
        $this->_metaDatas->namespace = $this->_data['namespace'];
        $driver->loadMetadataForClass($className, $this->_metaDatas);

        return $this;
    }

    protected function _createFolders()
    {
        //\WonderWp\trace($this->_data);
        $this->_folders = array();
        $this->_folders['base'] = WP_PLUGIN_DIR . '/' . sanitize_title($this->_data['name']);
        $this->_folders['includes'] = $this->_folders['base'] . '/includes';
        $this->_folders['admin'] = $this->_folders['base'] . '/admin';
        $this->_folders['languages'] = $this->_folders['base'] . '/languages';

        if (!empty($this->_folders)) {
            foreach ($this->_folders as $folder) {
                if (!is_dir($folder)) {
                    $this->_fileSystem->mkdir($folder, FS_CHMOD_DIR, true);
                }
            }
        }

        return $this;
    }

    protected function _generateIndexFile()
    {
        $indexTpl = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'index.php';
        $indexContent = $this->_fileSystem->get_contents($indexTpl);
        $indexFile = $this->_folders['base'] . DIRECTORY_SEPARATOR . 'index.php';
        $this->_fileSystem->put_contents($indexFile, $indexContent, FS_CHMOD_FILE);
        return $this;
    }

    protected function _generateBootsrtapFile()
    {
        $bootstrapTpl = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'plugin_bootstrap.php';
        $bootstrapContent = str_replace(
            array('__PLUGIN_NAME__', '__PLUGIN_SLUG__', '__PLUGIN_DESC__', '__PLUGIN_CONST__', '__PLUGIN_CONST_LOW__', '__PLUGIN_ENTITY__', '__PLUGIN_NS__', '__PLUGIN_CLASSNAME__'),
            array($this->_data['name'], sanitize_title($this->_data['name']), $this->_data['desc'], strtoupper($this->_data['entityname']), strtolower($this->_data['entityname']), $this->_data['entityname'], $this->_data['namespace'], $this->_data['className']),
            $this->_fileSystem->get_contents($bootstrapTpl)
        );
        $bootstrapFile = $this->_folders['base'] . DIRECTORY_SEPARATOR . sanitize_title($this->_data['name']) . '.php';
        $this->_fileSystem->put_contents($bootstrapFile, $bootstrapContent, FS_CHMOD_FILE);
        return $this;
    }

    protected function _generateManager()
    {
        $mgrTpl = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'Manager.php';
        $mgrContent = str_replace(
            array('__PLUGIN_NAME__', '__PLUGIN_SLUG__', '__PLUGIN_DESC__', '__PLUGIN_CONST__', '__PLUGIN_CONST_LOW__', '__PLUGIN_ENTITY__', '__PLUGIN_NS__', '__PLUGIN_CLASSNAME__', '__ESCAPED_PLUGIN_NS__'),
            array($this->_data['name'], sanitize_title($this->_data['name']), $this->_data['desc'], strtoupper($this->_data['entityname']), strtolower($this->_data['entityname']), $this->_data['entityname'], $this->_data['namespace'], $this->_data['className'], str_replace('\\', '\\\\', $this->_data['namespace'])),
            $this->_fileSystem->get_contents($mgrTpl)
        );
        $mgrFile = $this->_folders['includes'] . DIRECTORY_SEPARATOR . ($this->_data['entityname']) . 'Manager.php';
        $this->_fileSystem->put_contents($mgrFile, $mgrContent, FS_CHMOD_FILE);
        return $this;
    }

    protected function _generateActivator()
    {
        $acTpl = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'Activator.php';
        $acContent = str_replace(
            array('__PLUGIN_ENTITY__', '__PLUGIN_NS__'),
            array($this->_data['entityname'], $this->_data['namespace']),
            $this->_fileSystem->get_contents($acTpl)
        );
        $acFile = $this->_folders['includes'] . DIRECTORY_SEPARATOR . ($this->_data['entityname']) . 'Activator.php';
        $this->_fileSystem->put_contents($acFile, $acContent, FS_CHMOD_FILE);
        return $this;
    }

    protected function _generateDeActivator()
    {
        return $this;
    }

    protected function _generateAdminController()
    {
        $acTpl = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'AdminController.php';
        $acContent = str_replace(
            array('__PLUGIN_NAME__', '__PLUGIN_SLUG__', '__PLUGIN_ENTITY__', '__PLUGIN_NS__'),
            array(str_replace('wwp ', '', $this->_data['name']), sanitize_title($this->_data['name']), $this->_data['entityname'], $this->_data['namespace']),
            $this->_fileSystem->get_contents($acTpl)
        );
        $acFile = $this->_folders['admin'] . DIRECTORY_SEPARATOR . ($this->_data['entityname']) . 'AdminController.php';
        $this->_fileSystem->put_contents($acFile, $acContent, FS_CHMOD_FILE);
        return $this;
    }

    protected function _generateEntity()
    {

        $metaDatas = array();
        $metaDatas[] = $this->_metaDatas;

        //Use Doctrine entity generator
        $entityGenerator = new EntityGenerator();
        $entityGenerator->setGenerateAnnotations(true);
        $entityGenerator->setGenerateStubMethods(true);
        $entityGenerator->setRegenerateEntityIfExists(true);
        $entityGenerator->setUpdateEntityIfExists(true);
        $entityGenerator->setNumSpaces(true);
        $entityGenerator->setClassToExtend(AbstractEntity::class);
        $entityGenerator->generate($metaDatas, $this->_folders['includes']);

        //Entity has been generated with psr4 namespace metadatas, so we move it back to the includes folder
        $numOfDirsCreatedByNamespace = count(explode('\\', $this->_data['namespace']));
        $namespacedDest = $this->_folders['includes'] . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $this->_metaDatas->name) . '.php';
        $inludeDest = $this->_folders['includes'] . DIRECTORY_SEPARATOR . $this->_data['className'] . 'Entity.php';

        $acContent = str_replace(
            array('use Doctrine\ORM\Mapping as ORM;', '@ORM\\'),
            array('', '@'),
            $this->_fileSystem->get_contents($namespacedDest)
        );
        $acFile = $this->_folders['includes'] . DIRECTORY_SEPARATOR . ($this->_data['entityname']) . 'Form.php';
        $this->_fileSystem->put_contents($inludeDest, $acContent, FS_CHMOD_FILE);

        for ($i = 1; $i < $numOfDirsCreatedByNamespace; $i++) {
            $namespacedDest = dirname($namespacedDest);
        }
        //Delete namespace folder
        $deleted = $this->_fileSystem->rmdir(dirname($namespacedDest), true);

        return $this;
    }

    protected function _generateForm()
    {
        $acTpl = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'Form.php';
        $acContent = str_replace(
            array('__PLUGIN_ENTITY__', '__PLUGIN_NS__'),
            array($this->_data['entityname'], $this->_data['namespace']),
            $this->_fileSystem->get_contents($acTpl)
        );
        $acFile = $this->_folders['includes'] . DIRECTORY_SEPARATOR . ($this->_data['entityname']) . 'Form.php';
        $this->_fileSystem->put_contents($acFile, $acContent, FS_CHMOD_FILE);
        return $this;
    }

    protected function _generateListTable()
    {
        $acTpl = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'ListTable.php';
        $acContent = str_replace(
            array('__PLUGIN_ENTITY__', '__PLUGIN_NS__'),
            array($this->_data['entityname'], $this->_data['namespace']),
            $this->_fileSystem->get_contents($acTpl)
        );
        $acFile = $this->_folders['includes'] . DIRECTORY_SEPARATOR . ($this->_data['entityname']) . 'ListTable.php';
        $this->_fileSystem->put_contents($acFile, $acContent, FS_CHMOD_FILE);
        return $this;
    }

    protected function _generateHookService()
    {
        $acTpl = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'HookService.php';
        $acContent = str_replace(
            array('__PLUGIN_NAME__', '__PLUGIN_SLUG__', '__PLUGIN_ENTITY__', '__PLUGIN_NS__', '__PLUGIN_CONST__'),
            array(str_replace('wwp ', '', $this->_data['name']), sanitize_title($this->_data['name']), $this->_data['entityname'], $this->_data['namespace'], strtoupper($this->_data['entityname'])),
            $this->_fileSystem->get_contents($acTpl)
        );
        $acFile = $this->_folders['includes'] . DIRECTORY_SEPARATOR . ($this->_data['entityname']) . 'HookService.php';
        $this->_fileSystem->put_contents($acFile, $acContent, FS_CHMOD_FILE);
        return $this;
    }

    protected function _generateLanguages()
    {
        $container = Container::getInstance();
        $em = $container->offsetGet('entityManager');
        $repo = $em->getRepository(LangEntity::class);
        $langs = $repo->findAll();

        $fieldNames = $this->_metaDatas->fieldNames;

        $pot = '
msgid ""
msgstr ""
"X-Generator :wonderwp"';
        $po = array();

        if (!empty($fieldNames)) {
            foreach ($fieldNames as $fieldName) {
                $pot .= '
msgid "' . $fieldName . '.trad"
msgstr ""
';
                if (!empty($langs)) {
                    foreach ($langs as $lang) {
                        $locale = $lang->getLocale();
                        if (empty($po[$locale])) {
                            $current_user = wp_get_current_user();
                            $po[$locale] = 'msgid ""
msgstr ""
"POT-Creation-Date :'.date('r').'"
"PO-Revision-Date :'.date('r').'"
"Last-Translator : '.$current_user->user_login.' <'.$current_user->user_email.'>"
"X-Generator :wonderwp"
"X-Loco-Target-Locale :'.$locale.'"
';
                        }
                        $po[$locale] .= '
msgid "' . $fieldName . '.trad"
msgstr "' . ucfirst($fieldName) . '"
';
                    }
                }
            }
        }

        if (!empty($pot)) {
            $potFile = $this->_folders['languages'] . DIRECTORY_SEPARATOR . sanitize_title($this->_data['name']) . '.pot';
            $this->_fileSystem->put_contents($potFile, $pot, FS_CHMOD_FILE);
            if (!empty($po)) {
                foreach ($po as $locale => $localizedPo) {
                    $localizedPoFile = $this->_folders['languages'] . DIRECTORY_SEPARATOR . sanitize_title($this->_data['name']) . '-' . $locale . '.po';
                    $this->_fileSystem->put_contents($localizedPoFile, $localizedPo, FS_CHMOD_FILE);

                    if(class_exists(LocoAdmin::class)) {
                        $po = LocoAdmin::parse_po($localizedPoFile);
                        $mo = LocoAdmin::msgfmt_native($po);
                        $moPath = $this->_folders['languages'] . DIRECTORY_SEPARATOR . sanitize_title($this->_data['name']) . '-' . $locale . '.mo';
                        $bytes = file_put_contents($moPath, $mo);
                    }

                }
            }
        }

        return $this;
    }

    protected function _generateIcon()
    {
        $iconSrc = $this->_manager->getConfig('path.root') . DIRECTORY_SEPARATOR . 'deliverables' . DIRECTORY_SEPARATOR . 'icon.svg';
        $iconDest = $this->_folders['base'] . DIRECTORY_SEPARATOR . 'icon.svg';
        $this->_fileSystem->copy($iconSrc, $iconDest);
        return $this;
    }


}