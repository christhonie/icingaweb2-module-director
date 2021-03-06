<?php

namespace Icinga\Module\Director\Controllers;

use Icinga\Module\Director\Web\Controller\ActionController;
use dipl\Html\Html;
use dipl\Html\Link;

class SchemaController extends ActionController
{
    protected $schemas;

    public function init()
    {
        $this->schemas = [
            'mysql' => $this->translate('MySQL schema'),
            'pgsql' => $this->translate('PostgreSQL schema'),
        ];
    }

    public function mysqlAction()
    {
        $this->serveSchema('mysql');
    }

    public function pgsqlAction()
    {
        $this->serveSchema('pgsql');
    }

    protected function serveSchema($type)
    {
        $schema = $this->loadSchema($type);

        if ($this->params->get('format') === 'sql') {
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $type . '.sql');
            echo $schema;
            exit;
            // TODO: Shutdown
        }

        $this
            ->addSchemaTabs($type)
            ->addTitle($this->schemas[$type])
            ->addDownloadAction()
            ->content()->add(Html::pre($schema));
    }

    protected function loadSchema($type)
    {
        return file_get_contents(
            sprintf(
                '%s/schema/%s.sql',
                $this->Module()->getBasedir(),
                $type
            )
        );
    }

    protected function addDownloadAction()
    {
        $this->actions()->add(
            Link::create(
                $this->translate('Download'),
                $this->url()->with('format', 'sql'),
                null,
                [
                    'target' => '_blank',
                    'class'  => 'icon-download',
                ]
            )
        );

        return $this;
    }

    protected function addSchemaTabs($active)
    {
        $tabs = $this->tabs();
        foreach ($this->schemas as $type => $title) {
            $tabs->add($type, [
                'url'   => 'director/schema/' . $type,
                'label' => $title,
            ]);
        }

        $tabs->activate($active);

        return $this;
    }
}
