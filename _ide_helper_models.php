<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property string $id
 * @property string|null $job_title
 * @property string|null $work_start_date
 * @property string|null $birth_date
 * @property array|null $education
 * @property array|null $work_experience
 * @property int $email_sent
 * @property \Illuminate\Support\Carbon|null $last_viewed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereEducation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereEmailSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereLastViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereWorkExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereWorkStartDate($value)
 */
	class EmployeeCV extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $ladder
 * @property int $group
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereLadder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Position whereUpdatedAt($value)
 */
	class Position extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $ladder
 * @property int $group
 * @property array $salaries
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder whereLadder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder whereSalaries($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryLadder whereUpdatedAt($value)
 */
	class SalaryLadder extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

