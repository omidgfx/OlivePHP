<?php namespace Olive\Security;

use Olive\Http\Session;
use Olive\Util\Text;

/**
 * ## How to use?
 *
 * 1. Create a CSRFGuard for each forms by a unique key.
 * 2. Spawn a new token in the hidden field of your form.
 * 3. Check validity of the token on form submition.
 * 4. Revoke token after token validation.
 *
 * Class CSRFGuard
 * @package App\Core
 */
class CSRFGuard
{

    #region Const & Fields
    public const          TIMEOUT = 15;

    private const         MASTER_KEY   = 'csrf';
    private const         TOKEN_LENGTH = 16;

    /** @var string */
    private $key;

    /** @var array */
    private $tokens;
    #endregion

    #region Construct
    /**
     * CSRFToken constructor.
     * @param string $key
     */
    public function __construct($key) {
        $this->key = $key;
        $this->fill();
    }

    #endregion

    #region Public Functions

    /**
     * Generate new token for current CSRFGuard
     *
     * @param int $timeout In minutes
     * @return string
     */
    public function spawnToken($timeout = CSRFGuard::TIMEOUT) {
        $token          = Text::randomCryptography(self::TOKEN_LENGTH);
        $this->tokens[] = [$token, intval(time() + ($timeout * 60))];
        $this->save();
        return $token;
    }

    /**
     * Check if token is valid in current CSRFGuard
     *
     * @param string $token
     * @return bool
     */
    public function isValid($token) {
        $index = $this->find($token);
        return $index > -1 and $this->tokens[1] > time();
    }

    /**
     * Revoke (delete) token from current CSRFGuard
     *
     * @param string $token
     * @return bool
     */
    public function revokeToken($token) {
        $index = $this->find($token);
        if ($index > -1) {
            unset($this->tokens[$index]);
            $this->save();
            return true;
        }
        return false;
    }

    #endregion

    #region Private Functions

    /**
     * @param $token
     * @return int -1: not found, 0 < index of found item
     */
    private function find($token) {
        $len = count($this->tokens);
        for ($i = 0; $i < $len; $i++)
            if ($this->tokens[$i][0] === $token)
                return $i;
        return -1;
    }

    private function fill() {
        $this->tokens = self::getTokens($this->key);
    }

    private function save() {
        self::saveTokens($this->key, $this->tokens);
    }
    #endregion

    #region Statitcs

    public static function optimize() {
        $now = time();

        $optimized = [];

        $optimize = function (&$key, &$token, &$optimized) use (&$now) {
            if (count($token) == 2
                and intval($token[1]) >= $now
                and is_string($token[0])
                and strlen($token[0]) == self::TOKEN_LENGTH) {

                if (!key_exists($key, $optimized))
                    $optimized[$key] = [];
                $optimized[$key][] = [$token[0], intval($token[1])];
            }
        };

        $guards = self::readGuards();
        foreach ($guards as $key => &$tokens)
            foreach ($tokens as &$tokenArr)
                $optimize($key, $tokenArr, $optimized);

        count($optimized) > 0
            ? self::saveGuards($optimized)
            : self::eliminate();
    }

    public static function eliminate($key = null) {
        $guards = self::readGuards();
        if ($key != null) {
            if (key_exists($key, $guards)) {
                unset($guards[$key]);
                self::saveGuards($guards);
                self::optimize();
            }
        } else Session::delete(CSRFGuard::MASTER_KEY);
    }

    /**
     * @param string $key
     * @return array|[]
     */
    private static function getTokens($key) {
        $guards = self::readGuards();
        return key_exists($key, $guards) ? $guards[$key] : [];
    }

    /**
     * @param array $tokens
     * @param string $key
     */
    private static function saveTokens($key, $tokens) {
        $guards       = self::readGuards();
        $guards[$key] = $tokens;
        self::saveGuards($guards);
    }

    /**
     * @return array
     */
    protected static function readGuards() {
        return Session::get(CSRFGuard::MASTER_KEY, []);
    }

    /**
     * @param array $guards
     */
    protected static function saveGuards($guards) {
        Session::set(CSRFGuard::MASTER_KEY, $guards);
    }

    #endregion

}
