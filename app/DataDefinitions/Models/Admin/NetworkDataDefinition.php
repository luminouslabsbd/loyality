<?php

namespace App\DataDefinitions\Models\Admin;

use App\DataDefinitions\DataDefinition;
use Illuminate\Database\Eloquent\Model;

class NetworkDataDefinition extends DataDefinition
{
    /**
     * Unique for data definitions, url-friendly name for CRUD purposes.
     *
     * @var string
     */
    public $name = 'networks';

    /**
     * The model associated with the definition.
     *
     * @var Model
     */
    public $model;

    /**
     * Settings.
     *
     * @var array
     */
    public $settings;

    /**
     * Model fields for list, edit, view.
     *
     * @var array
     */
    public $fields;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Set the model
        $this->model = new \App\Models\Network;

        // Define the fields for the data definition
        $this->fields = [
            'name' => [
                'text' => trans('common.name'),
                'type' => 'string',
                'highlight' => true,
                'searchable' => true,
                'sortable' => true,
                'validate' => ['required', 'max:120'],
                'actions' => ['list', 'insert', 'edit', 'view', 'export'],
            ],/*
            'time_zone' => [
                'text' => trans('common.time_zone'),
                'type' => 'time_zone',
                'default' => auth('admin')->user()->time_zone,
                'validate' => ['required', 'timezone'],
                'actions' => ['view', 'edit', 'insert', 'export'],
            ],*/
            'currency' => [
                'text' => trans('common.currency'),
                'type' => 'currency',
                'default' => auth('admin')->user()->currency,
                'validate' => ['required'],
                'actions' => ['view', 'edit', 'insert', 'export'],
            ],
            'is_active' => [
                'text' => trans('common.active'),
                'type' => 'boolean',
                'validate' => ['nullable', 'boolean'],
                'format' => 'icon',
                'sortable' => true,
                'default' => true,
                'help' => trans('common.network_is_active_text'),
                'actions' => ['list', 'insert', 'edit', 'view', 'export'],
            ],
            'created_at' => [
                'text' => trans('common.created'),
                'type' => 'date_time',
                'actions' => ['view', 'export'],
            ],
            'created_by' => [
                'text' => trans('common.created_by'),
                'type' => 'user.admin',
                'actions' => ['view', 'export'],
            ],
            'updated_at' => [
                'text' => trans('common.updated'),
                'type' => 'date_time',
                'actions' => ['view', 'export'],
            ],
            'updated_by' => [
                'text' => trans('common.updated_by'),
                'type' => 'user.admin',
                'actions' => ['view', 'export'],
            ],
        ];

        // Define the general settings for the data definition
        $this->settings = [
            // Icon
            'icon' => 'cube-transparent',
            // Title (plural of subject)
            'title' => trans('common.networks'),
            // Override title (this title is shown in every view, without sub title like "Edit item" or "View item")
            'overrideTitle' => null,
            // Guard of user that manages this data (member, staff, partner or admin)
            // This guard is also used for routes and to include the correct Blade layout
            'guard' => 'admin',
            // If set, these role(s) are required
            'roles' => [1],
            // Used for updating forms like profile, where user has to enter current password in order to save
            'editRequiresPassword' => false,
            // If true, the visitor is redirected to the edit form
            'redirectListToEdit' => false,
            // This column has to match auth($guard)->user()->id if 'redirectListToEdit' == true (usually it will be 'id' or 'created_by')
            'redirectListToEditColumn' => null,
            // If true, the user id must match the created_by field
            'userMustOwnRecords' => false,
            // Should there be checkboxes for all rows
            'multiSelect' => true,
            // Default items per page for pagination
            'itemsPerPage' => 10,
            // Order by column
            'orderByColumn' => 'id',
            // Order direction, 'asc' or 'desc'
            'orderDirection' => 'desc',
            // Possible actions for the data
            'actions' => [
                'subject_column' => 'name', // This column is used for page titles and delete confirmations
                'list' => true,
                'insert' => true,
                'edit' => true,
                'delete' => true,
                'view' => true,
                'export' => true,
            ],
        ];
    }

    /**
     * Do not modify below this line.
     *
     * ---------------------------------
     */

    /**
     * Retrieve data based on fields.
     *
     * @param  string  $dateDefinitionName
     * @param  Model  $model
     */
    public function getData(string $dateDefinitionName = null, string $dateDefinitionView = 'list', array $options = [], Model $model = null, array $settings = [], array $fields = []): array
    {
        return parent::getData($this->name, $dateDefinitionView, $options, $this->model, $this->settings, $this->fields);
    }

    /**
     * Parse settings.
     */
    public function getSettings(array $settings): array
    {
        return parent::getSettings($this->settings);
    }
}
