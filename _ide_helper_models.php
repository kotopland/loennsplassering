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
 * @property string $id
 * @property string|null $job_title
 * @property string|null $work_start_date
 * @property string|null $birth_date
 * @property array<array-key, mixed>|null $personal_info Personal information for submission
 * @property string|null $status
 * @property string|null $generated_file_path
 * @property string|null $generated_file_timestamp
 * @property array<array-key, mixed>|null $education
 * @property array<array-key, mixed>|null $work_experience
 * @property int $email_sent
 * @property \Illuminate\Support\Carbon|null $last_viewed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\EmployeeCVFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereEducation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereEmailSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereGeneratedFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereGeneratedFileTimestamp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereLastViewed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV wherePersonalInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereWorkExperience($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeCV whereWorkStartDate($value)
 */
	class EmployeeCV extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $ladder
 * @property int $group
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\PositionFactory factory($count = null, $state = [])
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
 * @property int $id
 * @property string $ladder
 * @property int $group
 * @property array<array-key, mixed> $salaries
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
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $login_token
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLoginToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

