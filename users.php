<?

public function updateUsers($users)
{
	foreach ($users as $user) {
		try {
			if ($user['name'] && $user['login'] && $user['email'] && $user['password'] && strlen($user['name']) >= 10)
				DB::table('users')->where('id', $user['id'])->update($this->setData($user));
		} catch (\Throwable $e) {
			return Redirect::back()->withErrors(['error' => 'We couldn\'t update user: ' . $e->getMessage()]);
		}
	}
	return Redirect::back()->with(['success' => 'All users updated.']);
}

public function storeUsers($users)
{
    try {
        DB::table('users')->insert($this->prepareDataToStore($users));
        $this->sendEmail($users);
    } catch (\Throwable $e) {
        return Redirect::back()->withErrors(['error' => 'We couldn\'t store users']);
    }

    return Redirect::back()->with(['success' => 'All users created.']);
}

private function prepareDataToStore($users)
{
    $dataToStore = [];
    foreach ($users as $user) {
        if ($user['name'] && $user['login'] && $user['email'] && $user['password'] && strlen($user['name']) >= 10){
            $dataToStore[] = $this->setData($user);
        }
    }

    return $dataToStore;
}

private function setData($user)
{
    return [
        'name' => $user['name'],
        'login' => $user['login'],
        'email' => $user['email'],
        'password' => md5($user['password'])
    ];
}

private function sendEmail($users)
{
    foreach ($users as $user) {
        $message = 'Account has beed created. You can log in as <b>' . $user['login'] . '</b>';
        if ($user['email']) {
            Mail::to($user['email'])
                ->cc('support@company.com')
                ->subject('New account created')
                ->queue($message);
        }
    }
    return true;
}

/*
    W przypadku gdy dane przekazywane są z formularzy zastosowałbym kod, który zaproponowałem w funckji storeUsers,
    ponieważ w takim przypadku wymagamy, aby każdy user miał wszystkie niezbędne dane. Natomiast jeśli ta funkcja
    miałaby wywoływana byłaby dla danych pobranych np. z pliku, gdzie dane mogą być błędne i powinniśmy zapisać tylko
    tych użytkowników, którzy mają poprawne dane, to zostawiłbym kod, który był pierwotnie, tylko wyciągnąłbym kod,
    który się powtarza.

    W przypadku danych wysłanych z formularza:
    Utworzyłbym także klasę UserStoreAndUpdateRequest, w której zdefiniowałbym walidację dla danego requesta.
    W takim przypadku niepotrzebny byłby if w pętli.

    rules = [
        'users.name' => 'required|min:10'
        'users.login' => 'required'
        'users.email' => 'required|email'
        'users.password' => 'required'
    ]


    Po zastsowaniu walidacji można byłoby użyć paczkę  https://github.com/mavinoo/laravelBatch do wykonania updatu za
    pomocą jednego zapytania do bazy
*/

?>