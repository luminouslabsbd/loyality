<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\SecurityScheme(
 *     securityScheme="partner_auth_token",
 *     type="http",
 *     scheme="bearer",
 *     name="Authorization",
 *     description="Bearer {your_auth_token_here}"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="member_auth_token",
 *     type="http",
 *     scheme="bearer",
 *     name="Authorization",
 *     description="Bearer {your_auth_token_here}"
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     required={"message"},
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="The error message",
 *         example="The provided credentials are incorrect."
 *     ),
 *     @OA\Property(
 *         property="code",
 *         type="integer",
 *         description="The error code",
 *         example=400
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="UnauthenticatedResponse",
 *     required={"message"},
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="The error message",
 *         example="Unauthenticated."
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="NotFoundResponse",
 *     required={"message"},
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="The error message",
 *         example="Not found."
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ValidationErrorResponse",
 *     type="object",
 *     @OA\Property(
 *         property="message",
 *         type="string",
 *         description="The error message",
 *         example="The email must be a valid email address."
 *     ),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         description="The validation errors",
 *         @OA\Property(
 *             property="email",
 *             type="array",
 *             @OA\Items(type="string"),
 *             example={"The email must be a valid email address."}
 *         )
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="PartnerLoginSuccess",
 *     required={"token"},
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         description="The generated token for the partner",
 *         example="21|5XDpYGurS96daVl7LSr34s03tqn37kfPzkbkqP6D"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="MemberLoginSuccess",
 *     required={"token"},
 *     @OA\Property(
 *         property="token",
 *         type="string",
 *         description="The generated token for the member",
 *         example="21|5XDpYGurS96daVl7LSr34s03tqn37kfPzkbkqP6D"
 *     )
 * )
 * 
 * @OA\Schema(
 *     schema="MemberRegistration",
 *     type="object",
 *     @OA\Property(property="email", type="string", format="email", example="email@example.com"),
 *     @OA\Property(property="name", type="string", maxLength=64, example="John Doe"),
 *     @OA\Property(property="password", type="string", maxLength=48, nullable=true, example="mypassword"),
 *     @OA\Property(property="time_zone", type="string", nullable=true, example="America/New_York"),
 *     @OA\Property(property="locale", type="string", minLength=5, maxLength=12, nullable=true, example="en_US"),
 *     @OA\Property(property="currency", type="string", minLength=3, maxLength=3, nullable=true, example="USD"),
 *     @OA\Property(property="accepts_emails", type="integer", enum={0, 1}, example=1, nullable=true),
 *     @OA\Property(property="send_mail", type="integer", enum={0, 1}, example=0, nullable=true, description="If 1, send an email with the password to the newly created member."),
 * )
 *
 * @OA\Schema(
 *     schema="Member",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="50510834128001805"),
 *     @OA\Property(property="unique_identifier", type="string", example="700-857-223-945"),
 *     @OA\Property(property="name", type="string", maxLength=64, example="Member Name"),
 *     @OA\Property(property="email", type="string", format="email", example="member@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", example="2023-06-20T09:12:01.000000Z"),
 *     @OA\Property(property="locale", type="string", minLength=5, maxLength=12, nullable=true, example="en_US"),
 *     @OA\Property(property="currency", type="string", minLength=3, maxLength=3, nullable=true, example="USD"),
 *     @OA\Property(property="time_zone", type="string", nullable=true, example="America/New_York"),
 *     @OA\Property(property="accepts_emails", type="integer", enum={0, 1}, example=0, nullable=true),
 *     @OA\Property(property="number_of_times_logged_in", type="integer", example=6),
 *     @OA\Property(property="last_login_at", type="string", format="date-time", example="2023-07-13T13:20:57.000000Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-20T09:12:01.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-07-13T13:20:57.000000Z"),
 *     @OA\Property(property="avatar", type="string", nullable=true),
 * )
 *
 * @OA\Schema(
 *     schema="Partner",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="50510833079424500"),
 *     @OA\Property(property="network_id", type="string", example="50510725860431444"),
 *     @OA\Property(property="name", type="string", maxLength=64, example="Partner Name"),
 *     @OA\Property(property="email", type="string", format="email", example="partner@example.com"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", example="2023-06-20T09:12:01.000000Z"),
 *     @OA\Property(property="locale", type="string", minLength=5, maxLength=12, nullable=true, example="en_US"),
 *     @OA\Property(property="currency", type="string", minLength=3, maxLength=3, nullable=true, example="USD"),
 *     @OA\Property(property="time_zone", type="string", nullable=true, example="America/New_York"),
 *     @OA\Property(property="number_of_times_logged_in", type="integer", example=6),
 *     @OA\Property(property="last_login_at", type="string", format="date-time", example="2023-07-13T13:20:57.000000Z"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-20T09:12:01.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-07-13T13:20:57.000000Z"),
 *     @OA\Property(property="avatar", type="string", nullable=true),
 * )
 * 
 * @OA\Schema(
 *     schema="Club",
 *     required={"id", "name"},
 *     @OA\Property(property="id", type="string", example="51209532520667617"),
 *     @OA\Property(property="name", type="string", maxLength=120, example="Club name"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * 
 * @OA\Schema(
 *     schema="Card",
 *     required={
 *         "id", "club_id", "name", "head", "title", "description", "unique_identifier",
 *         "issue_date", "expiration_date", "bg_color", "bg_color_opacity", "text_color",
 *         "text_label_color", "qr_color_light", "qr_color_dark", "currency",
 *         "initial_bonus_points", "points_expiration_months", "currency_unit_amount",
 *         "points_per_currency", "min_points_per_purchase", "max_points_per_purchase",
 *         "is_visible_by_default", "is_visible_when_logged_in",
 *         "total_amount_purchased", "number_of_points_issued", "last_points_issued_at",
 *         "number_of_points_redeemed", "number_of_rewards_redeemed", "last_reward_redeemed_at",
 *         "views", "last_view", "created_by", "created_at", "updated_at"
 *     },
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         description="Unique identifier for the Card",
 *         example="50510829749146291"
 *     ),
 *     @OA\Property(
 *         property="club_id",
 *         type="string",
 *         description="Unique identifier for the Club",
 *         example="51209532520667617"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the Card"
 *     ),
 *     @OA\Property(
 *         property="head",
 *         type="object",
 *         description="Multilingual card name",
 *         @OA\Property(property="de_DE", type="string", example="Gesunde Ernährung"),
 *         @OA\Property(property="en_US", type="string", example="Healthy Eats"),
 *         @OA\Property(property="es_ES", type="string", example="Comida Saludable"),
 *         @OA\Property(property="fr_FR", type="string", example="Repas Sains"),
 *         @OA\Property(property="nl_NL", type="string", example="Gezond Eten"),
 *         @OA\Property(property="pt_BR", type="string", example="Comidas Saudáveis")
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="object",
 *         description="Multilingual card title",
 *         @OA\Property(property="de_DE", type="string", example="Salat Ersparnisse"),
 *         @OA\Property(property="en_US", type="string", example="Salad Savings"),
 *         @OA\Property(property="es_ES", type="string", example="Ahorros en Ensaladas"),
 *         @OA\Property(property="fr_FR", type="string", example="Économies sur les Salades"),
 *         @OA\Property(property="nl_NL", type="string", example="Salade Besparingen"),
 *         @OA\Property(property="pt_BR", type="string", example="Economia em Salada")
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="object",
 *         description="Multilingual card description",
 *         @OA\Property(property="de_DE", type="string", example="Belohnung für gesunde Entscheidungen!"),
 *         @OA\Property(property="en_US", type="string", example="Get rewarded for healthy choices!"),
 *         @OA\Property(property="es_ES", type="string", example="¡Obtén recompensas por elecciones saludables!"),
 *         @OA\Property(property="fr_FR", type="string", example="Soignez vos choix et soyez récompensé(e) !"),
 *         @OA\Property(property="nl_NL", type="string", example="Word beloond voor gezonde keuzes!"),
 *         @OA\Property(property="pt_BR", type="string", example="Seja recompensado por escolhas saudáveis!")
 *     ),
 *     @OA\Property(
 *             property="unique_identifier",
 *             type="string",
 *             description="Unique identifier of the Card"
 *     ),
 *     @OA\Property(
 *         property="issue_date",
 *         type="string",
 *         format="date-time",
 *         description="Issue date of the Card"
 *     ),
 *     @OA\Property(
 *         property="expiration_date",
 *         type="string",
 *         format="date-time",
 *         description="Expiration date of the Card"
 *     ),
 *     @OA\Property(
 *         property="bg_color",
 *         type="string",
 *         description="Background color of the Card",
 *         example="#CCCCCC"
 *     ),
 *     @OA\Property(
 *         property="bg_color_opacity",
 *         type="integer",
 *         format="int32",
 *         description="Background color opacity of the Card",
 *         example=80
 *     ),
 *     @OA\Property(
 *         property="text_color",
 *         type="string",
 *         description="Text color of the Card",
 *         example="#333333"
 *     ),
 *     @OA\Property(
 *         property="text_label_color",
 *         type="string",
 *         description="Text label color of the Card",
 *         example="#333333"
 *     ),
 *     @OA\Property(
 *         property="qr_color_light",
 *         type="string",
 *         description="QR code light color of the Card",
 *         example="#CCCCCC"
 *     ),
 *     @OA\Property(
 *         property="qr_color_dark",
 *         type="string",
 *         description="QR code dark color of the Card",
 *         example="#222222"
 *     ),
 *     @OA\Property(
 *         property="currency",
 *         type="string",
 *         description="Currency of the Card",
 *         example="USD"
 *     ),
 *     @OA\Property(
 *         property="initial_bonus_points",
 *         type="integer",
 *         format="int32",
 *         description="Initial bonus points of the Card"
 *     ),
 *     @OA\Property(
 *        property="points_expiration_months",
 *        type="integer",
 *        format="int32",
 *        description="Points expiration months of the Card"
 *     ),
 *     @OA\Property(
 *        property="currency_unit_amount",
 *        type="integer",
 *        format="int32",
 *        description="Currency unit amount of the Card"
 *     ),
 *     @OA\Property(
 *        property="points_per_currency",
 *        type="integer",
 *        format="int32",
 *        description="Points per currency of the Card"
 *     ),
 *     @OA\Property(
 *        property="min_points_per_purchase",
 *        type="integer",
 *        format="int32",
 *        description="Minimum points per purchase of the Card"
 *     ),
 *     @OA\Property(
 *        property="max_points_per_purchase",
 *        type="integer",
 *        format="int64",
 *        description="Maximum points per purchase of the Card"
 *     ),
 *     @OA\Property(
 *        property="is_visible_by_default",
 *        type="integer",
 *        format="int32",
 *        description="Status of the Card if it's visible by default"
 *     ),
 *     @OA\Property(
 *        property="is_visible_when_logged_in",
 *        type="integer",
 *        format="int32",
 *        description="Status of the Card if it's visible when logged in"
 *     ),
 *     @OA\Property(
 *        property="total_amount_purchased",
 *        type="integer",
 *        format="int64",
 *        description="Total amount purchased of the Card"
 *     ),
 *     @OA\Property(
 *        property="number_of_points_issued",
 *        type="integer",
 *        format="int64",
 *        description="Number of points issued for the Card"
 *     ),
 *     @OA\Property(
 *        property="last_points_issued_at",
 *        type="string",
 *        format="date-time",
 *        description="Last time points were issued for the Card"
 *     ),
 *     @OA\Property(
 *        property="number_of_points_redeemed",
 *        type="integer",
 *        format="int64",
 *        description="Number of points redeemed from the Card"
 *     ),
 *     @OA\Property(
 *        property="number_of_rewards_redeemed",
 *        type="integer",
 *        format="int32",
 *        description="Number of rewards redeemed from the Card"
 *     ),
 *     @OA\Property(
 *        property="last_reward_redeemed_at",
 *        type="string",
 *        format="date-time",
 *        description="Last time a reward was redeemed from the Card"
 *     ),
 *     @OA\Property(
 *        property="views",
 *        type="integer",
 *        format="int32",
 *        description="Number of views of the Card"
 *     ),
 *     @OA\Property(
 *        property="last_view",
 *        type="string",
 *        format="date-time",
 *        description="Last view of the Card"
 *     ),
 *     @OA\Property(
 *        property="meta",
 *        type="object",
 *        nullable=true,
 *        description="Meta of the Card"
 *     ),
 *     @OA\Property(
 *        property="created_at",
 *        type="string",
 *        format="date-time",
 *        description="Creation date of the Card"
 *     ),
 *     @OA\Property(
 *        property="updated_at",
 *        type="string",
 *        format="date-time",
 *        description="Update date of the Card"
 *     ),
 *     @OA\Property(
 *        property="balance",
 *        type="integer",
 *        format="int32",
 *        example=-1,
 *        description="The member's balance. This field has a value of 0 or higher for API calls where a member is authenticated. If the balance is not applicable or unavailable, its value is -1."
 *     ),
 * )
 * 
 * @OA\Schema(
 *     schema="StaffMember",
 *     type="object",
 *     @OA\Property(
 *          property="id",
 *          type="string",
 *          example="50510833641460081",
 *          description="The unique identifier of the staff member"
 *     ),
 *     @OA\Property(
 *          property="club_id",
 *          type="string",
 *          example="50510725994647600",
 *          description="The unique identifier of the club"
 *     ),
 *     @OA\Property(
 *          property="name",
 *          type="string",
 *          example="John Doe",
 *          description="The name of the staff member"
 *     ),
 *     @OA\Property(
 *          property="email",
 *          type="string",
 *          format="email",
 *          example="johndoe@example.com",
 *          description="The email of the staff member"
 *     ),
 *     @OA\Property(
 *          property="time_zone",
 *          type="string",
 *          example="America/New_York",
 *          description="The time zone of the staff member"
 *     ),
 *     @OA\Property(
 *          property="number_of_times_logged_in",
 *          type="integer",
 *          example=5,
 *          description="The number of times the staff member has logged in"
 *     ),
 *     @OA\Property(
 *          property="last_login_at",
 *          type="string",
 *          format="date-time",
 *          example="2023-01-01T00:00:00Z",
 *          description="The timestamp of the staff member's last login"
 *     ),
 *     @OA\Property(
 *          property="created_at",
 *          type="string",
 *          format="date-time",
 *          example="2023-01-01T00:00:00Z",
 *          description="The timestamp of when the staff member was created"
 *     ),
 *     @OA\Property(
 *          property="updated_at",
 *          type="string",
 *          format="date-time",
 *          example="2023-01-01T00:00:00Z",
 *          description="The timestamp of the last update of the staff member"
 *     ),
 *     @OA\Property(
 *          property="avatar",
 *          type="string",
 *          format="uri",
 *          example="https://example.com/files/31/icon-80x80-v2.png",
 *          description="The URL to the avatar image of the staff member"
 *     ),
 * )
 * 
 * The schema for a purchase request.
 *
 * @OA\Schema(
 *     schema="PurchaseRequest",
 *     type="object",
 *     required={"purchase_amount"},
 *     @OA\Property(
 *          property="purchase_amount",
 *          type="number",
 *          description="The amount of money that was spent on the purchase."
 *     ),
 *     @OA\Property(
 *          property="note",
 *          type="string",
 *          description="An optional note that can be added to the purchase.",
 *          nullable=true
 *     ),
 * )
 *
 * The schema for a transaction.
 * 
 * @OA\Schema(
 *     schema="Transaction",
 *     type="object",
 *     @OA\Property(
 *         property="staff_id",
 *         type="string",
 *         description="The unique identifier of the staff member who created the transaction",
 *         example="50510833641460081"
 *     ),
 *     @OA\Property(
 *         property="member_id",
 *         type="string",
 *         description="The unique identifier of the member who made the purchase",
 *         example="50510834128001805"
 *     ),
 *     @OA\Property(
 *         property="card_id",
 *         type="string",
 *         description="The unique identifier of the card that was used for the purchase",
 *         example="50510826515340561"
 *     ),
 *     @OA\Property(
 *         property="partner_name",
 *         type="string",
 *         description="The name of the partner",
 *         example="Partner Name"
 *     ),
 *     @OA\Property(
 *         property="partner_email",
 *         type="string",
 *         description="The email of the partner",
 *         example="partner@example.com"
 *     ),
 *     @OA\Property(
 *         property="purchase_amount",
 *         type="number",
 *         description="The amount of money that was spent on the purchase"
 *     ),
 *     @OA\Property(
 *         property="note",
 *         type="string",
 *         description="An optional note that can be added to the purchase",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="points_issued",
 *         type="integer",
 *         format="int64",
 *         description="The number of points issued for the purchase"
 *     ),
 *     @OA\Property(
 *         property="reward_redeemed",
 *         type="boolean",
 *         description="Indicates whether a reward was redeemed for the purchase"
 *     ),
 *     @OA\Property(
 *         property="reward_id",
 *         type="string",
 *         description="The unique identifier of the reward that was redeemed",
 *         example="50510834538491954"
 *     ),
 *     @OA\Property(
 *         property="transaction_date",
 *         type="string",
 *         format="date-time",
 *         description="The timestamp of the transaction"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="The timestamp of when the transaction was created"
 *     ),
 * )
 */

class OpenApiSchemas
{
}
