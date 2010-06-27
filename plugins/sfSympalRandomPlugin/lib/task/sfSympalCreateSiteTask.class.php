<?php

/**
 * Task for creating a new sympal site.
 * 
 * This task performs the following things:
 *  * creates the symfony application if it doesn't exist
 *  * Calls the sympal:enable-for-app task, which performs the basic setup
 *    that the application needs (actually modifies files) to be a sympal app
 * 
 * @package     sfSympalPlugin
 * @subpackage  task
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @version     svn:$Id$ $Author$
 */
class sfSympalCreateSiteTask extends sfSympalBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('site', sfCommandArgument::REQUIRED, 'The site'),
    ));

    $this->addOptions(array(
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
    ));

    $this->aliases = array();
    $this->namespace = 'sympal';
    $this->name = 'create-site';
    $this->briefDescription = 'Install the sympal plugin content management framework.';

    $this->detailedDescription = <<<EOF
The [sympal:create-site|INFO] task will create a new Sympal site in the database
and generate the according symfony application.

  [./sympal:create-site my_site|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    if (!$options['no-confirmation'] && !$this->askConfirmation(array(sprintf('You are about to create a new site named "%s"', $arguments['site']), 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false))
    {
      $this->logSection('sympal', 'Install task aborted');

      return 1;
    }

    // Generate the application if it doesn't exist
    $this->_generateApplication($arguments['site']);
    
    // Prepare the application
    $this->_prepareApplication($arguments['site']);

    $databaseManager = new sfDatabaseManager($this->configuration);
    $site = $this->_getOrCreateSite($arguments, $options);

    // Copy site fixtures
    $this->_copyFixtures($arguments['site']);
  }

  /**
   * Ensures that the site record has been created
   */
  protected function _getOrCreateSite($arguments, $options)
  {
    $site = Doctrine_Core::getTable('sfSympalSite')
      ->createQuery('s')
      ->where('s.slug = ?', $arguments['site'])
      ->fetchOne();
    if (!$site)
    {
      $this->logSection('sympal', 'Creating new site record in database...');
      $site = new sfSympalSite();
      $site->title = sfInflector::humanize($arguments['site']);
      $site->slug = $arguments['site'];
    }

    $site->save();
    
    return $site;
  }

  /**
   * Generates an application by the given name if one doesn't exist
   */
  protected function _generateApplication($application)
  {
    try
    {
      $task = new sfGenerateAppTask($this->dispatcher, $this->formatter);
      $task->run(array($application), array());
    }
    catch (Exception $e)
    {
      // In case the app already exists, swallow the error
    }
  }

  protected function _prepareApplication($application)
  {
    $task = new sfSympalEnableForAppTask($this->dispatcher, $this->formatter);
    $task->run(array($application), array());
  }

  /**
   * Copies the site fixtures from the plugin directories to the
   * data/fixtures/app_name directory
   * 
   * @param string $application
   */
  protected function _copyFixtures($application)
  {
    $targetDir = sfConfig::get('sf_data_dir').'/fixtures/sympal/'.$application;
    
    $this->logSection('fixtures', sprintf('Coping "site" fixtures into data/fixture/sympal/%s directory', $application));

    // get all the "sympal" plugins
    $paths = $this->configuration
      ->getPluginConfiguration('sfSympalPlugin')
      ->getSympalConfiguration()
      ->getPluginPaths();

    // process the yaml files in /data/fixtures/project/*.sample.yml of each plugin
    foreach ($paths as $path)
    {
      $yamls = sfFinder::type('file')
        ->name('*.yml.sample')
        ->in($path.'/data/fixtures/site');
      
      foreach ($yamls as $yaml)
      {
        sfSympalInstallToolkit::processSampleYamlFile(
          $yaml,
          $targetDir,
          $this
        );
      }
    }
  }
}