<?php

namespace App\DataDefinitions\Models\Admin;

use App\DataDefinitions\DataDefinition;
use Illuminate\Database\Eloquent\Model;
use App\Models\Club;
use Illuminate\Support\Facades\Http;
use Mockery\Exception;
use function Laravel\Prompts\select;

class PartnerDataDefinition extends DataDefinition
{
    /**
     * Unique for data definitions, url-friendly name for CRUD purposes.
     *
     * @var string
     */
    public $name = 'partners';

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

    public static $crmUrl;


    /**
     * Constructor.
     */
    public function __construct()
    {
        // Set the model
        $this->model = new \App\Models\Partner;
        self::$crmUrl = env('CRM_API_URL');
        //self::getPackages();

            // Define the fields for the data definition
        $this->fields = [
            'avatar' => [
                'thumbnail' => 'small', // Image conversion used for list
                'conversion' => 'medium', // Image conversion used for view/edit
                'text' => trans('common.avatar'),
                'type' => 'avatar',
                'textualAvatarBasedOnColumn' => 'name',
                'accept' => 'image/svg+xml, image/png, image/jpeg, image/gif',
                'validate' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:1024', 'dimensions:min_width=60,min_height=60,max_width=1024,max_height=1024'],
                'classes::list' => 'md-only:hidden',
                'actions' => ['list', 'insert', 'edit', 'view'],
            ],
            'network_id' => [
                'text' => trans('common.network'),
                'highlight' => true,
                'filter' => true,
                'type' => 'belongsTo',
                'relation' => 'network',
                'relationKey' => 'networks.id',
                'relationValue' => 'networks.name',
                'relationModel' => new \App\Models\Network,
                'relationUserRoleFilter' => [2 => function ($model) {
                    // Get the network associated with the authenticated user
                    $userFilter = auth('admin')->user()->networks;

                    // Add a filter to the query based on the network relationship
                    return $model->query()->whereHas('admins', function ($q) use ($userFilter) {
                        $q->whereIn('networks.id', $userFilter->pluck('id'));
                    });
                }],
                'validate' => ['required'],
                'actions' => ['list', 'insert', 'edit', 'view', 'export'],
            ],
            'name' => [
                'text' => trans('common.name'),
                'type' => 'string',
                'searchable' => true,
                'sortable' => true,
                'validate' => ['required', 'max:120'],
                'classes::list' => 'md-only:hidden',
                'actions' => ['list', 'insert', 'edit', 'view', 'export'],
            ],
            'email' => [
                'text' => trans('common.email_address'),
                'type' => 'string',
                'format' => 'email',
                'searchable' => true,
                'sortable' => true,
                'validate' => ['required', 'email', 'max:120', 'unique:partners,email,:id'],
                'actions' => ['list', 'insert', 'edit', 'view', 'export'],
            ],
            'phone' => [
                'text' => trans('common.phone_number'),
                'type' => 'string',
                'format' => 'string',
                'searchable' => true,
                'sortable' => true,
                'validate' => ['required', 'max:16'],
                'actions' => ['list', 'insert', 'edit', 'view', 'export'],
            ],
            'password' => [
                'text' => trans('common.password'),
                'type' => 'password',
                'generatePasswordButton' => true,
                'mailUserPassword' => true,
                'validate' => ['nullable', 'min:6', 'max:48'],
                'help' => trans('common.new_password_text'),
                'actions' => ['insert', 'edit'],
            ],
            'password::insert' => [
                'text' => trans('common.password'),
                'type' => 'password',
                'generatePasswordButton' => true,
                'mailUserPassword' => true,
                'mailUserPasswordChecked' => true,
                'validate' => ['required', 'min:6', 'max:48'],
            ],
            'locale' => [
                'text' => trans('common.language'),
                'type' => 'locale',
                'default' => app()->make('i18n')->language->current->locale,
                'validate' => ['required'],
                'actions' => ['view', 'edit', 'insert', 'export'],
            ],
            'time_zone' => [
                'text' => trans('common.time_zone'),
                'type' => 'time_zone',
                'default' => auth('admin')->user()->time_zone,
                'validate' => ['required', 'timezone'],
                'actions' => ['view', 'edit', 'insert', 'export'],
            ],
            'currency' => [
                'text' => trans('common.currency'),
                'type' => 'currency',
                'default' => auth('admin')->user()->currency,
                'validate' => ['required'],
                'actions' => ['view', 'edit', 'insert', 'export'],
            ],
            'crm_domain' => [
                'text' => trans('common.domain'),
                'type' => 'string',
                'placeholder' => 'example',
                'validate' => ['required'],
                'actions' => ['view', 'edit', 'insert', 'export'],
            ],
            'crm_package_id' => [
                'text' => trans('common.package_id'),
                'highlight' => true,
                'filter' => false,
                'type' => 'select',
                'placeholder' => trans('common.package_id'),
                'validate' => ['required'],
                'options' => array_column(self::getPackages(), 'name', 'id'),
                'actions' => ['list', 'insert', 'edit', 'view', 'export'],
            ],
            'crm_billing_cycle' => [
                'text' => trans('common.billing_cycle'),
                'highlight' => true,
                'filter' => false,
                'type' => 'select',
                'placeholder' => trans('common.billing_cycle'),
                'validate' => ['required'],
                'options' => array_column([
                    [
                        'name' => "Monthly Price",
                        'id' => "monthly_price"
                    ],
                    [
                        'name' => "Lifetime Price",
                        'id' => "lifetime_price"
                    ]
                ], 'name', 'id'),
                'actions' => ['insert', 'edit', 'view', 'export'],
            ],
            'cards_on_homepage' => [
                'text' => trans('common.can_display_cards_on_homepage'),
                'type' => 'boolean',
                'json' => 'meta',
                'validate' => ['nullable', 'boolean'],
                'format' => 'icon', // Boolean format
                'default' => true,
                'help' => trans('common.can_display_cards_on_homepage_text'),
                'actions' => ['insert', 'edit', 'view', 'export'],
            ],
            'is_active' => [
                'text' => trans('common.active'),
                'type' => 'boolean',
                'validate' => ['nullable', 'boolean'],
                'format' => 'icon',
                'sortable' => true,
                'default' => true,
                'help' => trans('common.partner_is_active_text'),
                'classes::list' => 'lg-only:hidden',
                'actions' => ['list', 'insert', 'edit', 'view', 'export'],
            ],
            'number_of_times_logged_in' => [
                'text' => trans('common.logins'),
                'type' => 'number',
                'classes::list' => 'lg-only:hidden',
                'actions' => ['list', 'export'],
            ],
            'last_login_at' => [
                'text' => trans('common.last_login'),
                'type' => 'date_time',
                'default' => trans('common.never'),
                'classes::list' => 'lg-only:hidden',
                'actions' => ['list', 'export'],
            ],
            'login_as' => [
                'text' => trans('common.log_in'),
                'type' => 'impersonate',
                'guard' => 'partner',
                'actions' => ['list'],
            ],
            'loyalty_cards' => [
                'text' => trans('common.loyalty_cards'),
                'type' => 'query',
                'default' => '-',
                'classes::list' => 'lg-only:hidden',
                'actions' => ['list', 'export'],
                'format' => 'number',
                'query' => function ($row) {
                    // Return the amount of loyalty cards related to this partner
                    return $row->cards->count();
                },
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
            'icon' => 'building-storefront',
            // Title (plural of subject)
            'title' => trans('common.partners'),
            // Override title (this title is shown in every view, without sub title like "Edit item" or "View item")
            'overrideTitle' => null,
            // Guard of user that manages this data (member, staff, partner or admin)
            // This guard is also used for routes and to include the correct Blade layout
            'guard' => 'admin',
            // The guard used for sending the user password
            'mailUserPasswordGuard' => 'partner',
            // Used for updating forms like profile, where user has to enter current password in order to save
            'editRequiresPassword' => false,
            // If true, the visitor is redirected to the edit form
            'redirectListToEdit' => false,
            // This column has to match auth($guard)->user()->id if 'redirectListToEdit' == true (usually it will be 'id' or 'created_by')
            'redirectListToEditColumn' => null,
            // If true, the user id must match the created_by field
            'userMustOwnRecords' => false,
            // Filter with certain role
            'userFilterRole' => [2 => function ($model) {
                // Get the network associated with the authenticated user
                $userFilter = auth('admin')->user()->networks;

                // Add a filter to the query based on the network relationship
                return $model->query()->whereHas('network', function ($q) use ($userFilter) {
                    $q->whereIn('networks.id', $userFilter->pluck('id'));
                });
            }],
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
            // Callback after insert
            'afterInsert' => function ($model) {
                try {
                    // Insert default club record for newly created partner ($model)
                    $data = [
                        'name' => app('translator')->get('common.general', [], $model->locale),
                        'is_active' => true,
                    ];

                    // Create the Club
                    $model->clubs()->create($data);
                } catch (\Exception $e) {
                    report($e);
                    \Log::error($e);
                }
            },
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
     * @param string|null $dateDefinitionName
     * @param string $dateDefinitionView
     * @param array $options
     * @param Model|null $model
     * @param array $settings
     * @param array $fields
     * @return array
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

    public static function getPackages(): array|string
    {
        try {
            $packages = [];
            $response = Http::get(self::$crmUrl."/get_all_saas_crm_package");
            if ($response->successful()) {
                $data = $response->json();
                foreach ($data['packages'] as $package) {
                    $packages[] = [
                        'id' => $package['id'],
                        'name' => $package['name']
                    ];
                }
            }

            return $packages;
        }catch (Exception $exception){
            return "Problem of KEOS getPackages Method.".$exception;
        }
    }
}
