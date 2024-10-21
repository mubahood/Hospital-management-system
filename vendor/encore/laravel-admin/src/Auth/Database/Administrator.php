<?php

namespace Encore\Admin\Auth\Database;

use App\Models\Campus;
use App\Models\Company;
use App\Models\UserHasProgram;
use App\Models\Utils;
use Carbon\Carbon;
use Encore\Admin\Traits\DefaultDatetimeFormat;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;
    use HasPermissions;
    use DefaultDatetimeFormat;

    //this model to array, id and name
    public static function toSelectArray()
    {
        $administrators = Administrator::all();
        $administrators_array = [];
        foreach ($administrators as $administrator) {
            $administrators_array[$administrator->id] = $administrator->name;
        }
        return $administrators_array;
    }

    //company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    //function to get list of this model in array for select
    public static function get_list()
    {
        $list = [];
        $users = Administrator::all();
        foreach ($users as $u) {
            $list[$u->id] = $u->name;
        }
        return $list;
    }


    protected $fillable = ['username', 'password', 'name', 'avatar', 'created_at_text'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($m) {

            if (!Utils::validateEmail($m->email)) {
                // throw new \Exception("Invalid email address");
            }

            $n = $m->first_name . " " . $m->last_name;
            if (strlen(trim($n)) > 1) {
                $m->name = trim($n);
            }
            $m->username = $m->email;
            if ($m->password == null || strlen($m->password) < 2) {
                $m->password = password_hash('123456', PASSWORD_DEFAULT);
            }
        });
        self::updating(function ($m) {
            if (!Utils::validateEmail($m->email)) {
                // throw new \Exception("Invalid email address");
            }
            $n = $m->first_name . " " . $m->last_name;
            if (strlen(trim($n)) > 1) {
                $m->name = trim($n);
            }
            $m->username = $m->email;
        });
    }


    /**
     * Get avatar attribute.
     *
     * @param string $avatar
     *
     * @return string
     */
    public function getAvatarAttribute($avatar)
    {
        if (url()->isValidUrl($avatar)) {
            return $avatar;
        }

        $disk = config('admin.upload.disk');

        if ($avatar && array_key_exists($disk, config('filesystems.disks'))) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        $default = config('admin.default_avatar') ?: '/assets/images/user.jpg';

        return admin_asset($default);
    }


    public function programs()
    {
        return $this->hasMany(UserHasProgram::class, 'user_id');
    }

    public function program()
    {
        $p = UserHasProgram::where(['user_id' => $this->id])->first();
        if ($p == null) {
            $p = new UserHasProgram();
            $p->name = "No program";
        }
        return $p;
    }


    public function campus()
    {
        return $this->belongsTo(Campus::class, 'campus_id');
    }

    public function getCreatedAtTextAttribute($name)
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }


    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
    }
}
