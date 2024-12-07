<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\Passwords;


/**
 * Manages user-related operations such as authentication and adding new users.
 */
final class UserFacade implements Nette\Security\Authenticator
{
	// Minimum password length requirement for users
	public const PasswordMinLength = 7;

	// Database table and column names
	private const                    
		TableName = 'users',
		ColumnId = 'id',
		ColumnName = 'username',
		ColumnFirstname = 'firstname',
		ColumnLastname = 'lastname',
		ColumnPasswordHash = 'password',
		ColumnEmail = 'email',
		ColumnPhone = 'phone',
		ColumnBirthdate = 'birthdate',
		ColumnAddress = 'address',
		ColumnCity = 'city',
		ColumnRole = 'role';


	// Dependency injection of database explorer and password utilities
	public function __construct(
		private Nette\Database\Explorer $database,
		private Passwords $passwords,
	) {
	}

    public function getUsers()
    {
        return $this->database->table('users');
    }

    public function getUserById($id)
    {
        return $this->database->table('users')->get($id);
    }
	public function getUserByUsername(string $username)
    {
        return $this->database->table('users')->where('username', $username)->fetch();
    }
	public function delete(int $userId) {
		$user = $this->database->table("users")->get($userId);
		$user->delete();
	}



    public function updateUser($userId, $values)
    {
        $user = $this->database->table('users')->get($userId);

        if (isset($values['password'])) {
            $values['password'] = $this->passwords->hash($values['password']);
        }

        $user->update($values);
    }

public function updateUserImage($userId, $imagePath)
{
	$user = $this->database->table('users')->get($userId);

	if ($user === null) {
		throw new \Exception("User with ID $userId not found.");
	}

	$user->update([
		'image' => $imagePath,
	]);
}
	

	/**
	 * Authenticate a user based on provided credentials.
	 * Throws an AuthenticationException if authentication fails.
	 */
	public function authenticate(string $username, string $password): Nette\Security\SimpleIdentity
	{
		// Fetch the user details from the database by username
		$row = $this->database->table(self::TableName)
			->where(self::ColumnName, $username)
			->fetch();

		// Authentication checks
		if (!$row) {
			throw new Nette\Security\AuthenticationException('Uživatelské jméno je nesprávné.', self::IdentityNotFound);

		} elseif (!$this->passwords->verify($password, $row[self::ColumnPasswordHash])) {
			throw new Nette\Security\AuthenticationException('Heslo je nesprávné.', self::InvalidCredential);

		} elseif ($this->passwords->needsRehash($row[self::ColumnPasswordHash])) {
			$row->update([
				self::ColumnPasswordHash => $this->passwords->hash($password),
			]);
		}

		// Return user identity without the password hash
		$arr = $row->toArray();
		unset($arr[self::ColumnPasswordHash]);
		return new Nette\Security\SimpleIdentity($row[self::ColumnId], $row[self::ColumnRole], $arr);
	}


	/**
	 * Add a new user to the database.
	 * Throws a DuplicateNameException if the username is already taken.
	 */
	public function add(string $username, string $firstname, string $lastname, string $email, string $password, string $phone, string $birthdate, string $address, string $city): void 
		
	{
		// Validate the email format
		Nette\Utils\Validators::assert($email, 'email');

		// Attempt to insert the new user into the database
		try {
			$this->database->table(self::TableName)->insert([
				self::ColumnName => $username,
				self::ColumnFirstname => $firstname,
				self::ColumnLastname => $lastname,
				self::ColumnPasswordHash => $this->passwords->hash($password),
				self::ColumnEmail => $email,
				self::ColumnPhone => $phone,
        		self::ColumnBirthdate => $birthdate,
        		self::ColumnAddress => $address,
        		self::ColumnCity => $city,
				
			]);
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		} 
	}
}

/**
 * Custom exception for duplicate usernames.
 */
class DuplicateNameException extends \Exception
{
}
