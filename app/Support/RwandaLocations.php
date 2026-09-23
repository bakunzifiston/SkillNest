<?php

namespace App\Support;

class RwandaLocations
{
    public const DEFAULT_COUNTRY = 'Rwanda';

    /**
     * Countries offered at registration. Rwanda is first / default.
     *
     * @return list<string>
     */
    public static function countries(): array
    {
        return [
            'Rwanda',
            'Burundi',
            'Democratic Republic of the Congo',
            'Kenya',
            'Tanzania',
            'Uganda',
            'Other',
        ];
    }

    /**
     * Province => District => list of Sectors.
     *
     * @return array<string, array<string, list<string>>>
     */
    public static function tree(): array
    {
        return [
            'Kigali City' => [
                'Gasabo' => ['Bumbogo', 'Gatsata', 'Gikomero', 'Gisozi', 'Jabana', 'Jali', 'Kacyiru', 'Kimihurura', 'Kimironko', 'Kinyinya', 'Ndera', 'Nduba', 'Remera', 'Rusororo', 'Rutunga'],
                'Kicukiro' => ['Gahanga', 'Gatenga', 'Gikondo', 'Kagarama', 'Kanombe', 'Kicukiro', 'Kigarama', 'Masaka', 'Niboye', 'Nyarugunga'],
                'Nyarugenge' => ['Gitega', 'Kanyinya', 'Kigali', 'Kimisagara', 'Mageragere', 'Muhima', 'Nyakabanda', 'Nyamirambo', 'Nyarugenge', 'Rwezamenyo'],
            ],
            'Eastern Province' => [
                'Bugesera' => ['Gashora', 'Juru', 'Kamabuye', 'Ntarama', 'Mayange', 'Musenyi', 'Mwogo', 'Ngeruka', 'Nyamata', 'Nyarugenge', 'Rilima', 'Ruhuha', 'Rweru', 'Shyara', 'Mareba'],
                'Gatsibo' => ['Gasange', 'Gatsibo', 'Gitoki', 'Kabarore', 'Kageyo', 'Kiramuruzi', 'Kiziguro', 'Muhura', 'Murambi', 'Ngarama', 'Nyagihanga', 'Remera', 'Rugarama', 'Rwimbogo'],
                'Kayonza' => ['Gahini', 'Kabare', 'Kabarondo', 'Mukarange', 'Murundi', 'Mwiri', 'Ndego', 'Nyamirama', 'Rukara', 'Ruramira', 'Rwinkwavu'],
                'Kirehe' => ['Gahara', 'Gatore', 'Kigarama', 'Kigina', 'Kirehe', 'Mahama', 'Mpanga', 'Musaza', 'Mushikiri', 'Nasho', 'Nyamugari', 'Nyarubuye'],
                'Ngoma' => ['Gashanda', 'Jarama', 'Karembo', 'Kazo', 'Kibungo', 'Mugesera', 'Murama', 'Mutenderi', 'Remera', 'Rukira', 'Rukumberi', 'Rurenge', 'Sake', 'Zaza'],
                'Nyagatare' => ['Gatunda', 'Karama', 'Karangazi', 'Katabagemu', 'Kiyombe', 'Matimba', 'Mimuri', 'Mukama', 'Musheri', 'Nyagatare', 'Rukomo', 'Rwempasha', 'Rwimiyaga', 'Tabagwe'],
                'Rwamagana' => ['Fumbwe', 'Gahengeri', 'Gishari', 'Karenge', 'Kigabiro', 'Muhazi', 'Munyaga', 'Munyiginya', 'Musha', 'Muyumbu', 'Mwulire', 'Nyakariro', 'Nzige', 'Rubona'],
            ],
            'Northern Province' => [
                'Burera' => ['Bungwe', 'Butaro', 'Cyanika', 'Cyeru', 'Gahunga', 'Gatebe', 'Gitovu', 'Kagogo', 'Kinoni', 'Kinyababa', 'Kivuye', 'Nemba', 'Rugarama', 'Rugendabari', 'Ruhunde', 'Rusarabuye', 'Rwerere'],
                'Gakenke' => ['Busengo', 'Coko', 'Cyabingo', 'Gakenke', 'Gashenyi', 'Janja', 'Kamubuga', 'Karambo', 'Kivuruga', 'Mataba', 'Minazi', 'Mugunga', 'Muhondo', 'Muyongwe', 'Muzo', 'Nemba', 'Ruli', 'Rusasa', 'Rushashi'],
                'Gicumbi' => ['Bukure', 'Bwisige', 'Byumba', 'Cyumba', 'Giti', 'Kaniga', 'Manyagiro', 'Miyove', 'Kageyo', 'Mukarange', 'Muko', 'Mutete', 'Nyamiyaga', 'Nyankenke', 'Rubaya', 'Rukomo', 'Rushaki', 'Rutare', 'Ruvune', 'Rwamiko', 'Shangasha'],
                'Musanze' => ['Busogo', 'Cyuve', 'Gacaca', 'Gashaki', 'Gataraga', 'Kimonyi', 'Kinigi', 'Muhoza', 'Muko', 'Musanze', 'Nkotsi', 'Nyange', 'Remera', 'Rwaza', 'Shingiro'],
                'Rulindo' => ['Base', 'Burega', 'Bushoki', 'Buyoga', 'Cyinzuzi', 'Cyungo', 'Kinihira', 'Kisaro', 'Masoro', 'Mbogo', 'Murambi', 'Ngoma', 'Ntarabana', 'Rukozo', 'Rusiga', 'Shyorongi', 'Tumba'],
            ],
            'Southern Province' => [
                'Gisagara' => ['Gikonko', 'Gishubi', 'Kansi', 'Kibirizi', 'Kigembe', 'Mamba', 'Muganza', 'Mugombwa', 'Mukindo', 'Musha', 'Ndora', 'Nyanza', 'Save'],
                'Huye' => ['Gishamvu', 'Huye', 'Karama', 'Kigoma', 'Kinazi', 'Maraba', 'Mbazi', 'Mukura', 'Ngoma', 'Ruhashya', 'Rusatira', 'Rwaniro', 'Simbi', 'Tumba'],
                'Kamonyi' => ['Gacurabwenge', 'Karama', 'Kayenzi', 'Kayumbu', 'Mugina', 'Musambira', 'Ngamba', 'Nyamiyaga', 'Nyarubaka', 'Rugarika', 'Rukoma', 'Runda'],
                'Muhanga' => ['Cyeza', 'Kabacuzi', 'Kibangu', 'Kiyumba', 'Muhanga', 'Mushishiro', 'Nyabinoni', 'Nyamabuye', 'Nyarusange', 'Rongi', 'Rugendabari', 'Shyogwe'],
                'Nyamagabe' => ['Buruhukiro', 'Cyanika', 'Gasaka', 'Gatare', 'Kaduha', 'Kamegeri', 'Kibirizi', 'Kibumbwe', 'Kitabi', 'Mbazi', 'Mugano', 'Musange', 'Musebeya', 'Mushubi', 'Nkomane', 'Tare', 'Uwinkingi'],
                'Nyanza' => ['Busasamana', 'Busoro', 'Cyabakamyi', 'Kibirizi', 'Kigoma', 'Mukingo', 'Muyira', 'Ntyazo', 'Nyagisozi', 'Rwabicuma'],
                'Nyaruguru' => ['Cyahinda', 'Busanze', 'Kibeho', 'Kivu', 'Mata', 'Muganza', 'Munini', 'Ngera', 'Ngoma', 'Nyabimata', 'Nyagisozi', 'Ruheru', 'Ruramba', 'Rusenge'],
                'Ruhango' => ['Bweramana', 'Byimana', 'Kabagali', 'Kinazi', 'Kinihira', 'Mbuye', 'Mwendo', 'Ntongwe', 'Ruhango'],
            ],
            'Western Province' => [
                'Karongi' => ['Bwishyura', 'Gashari', 'Gishyita', 'Gitesi', 'Mubuga', 'Murambi', 'Murundi', 'Mutuntu', 'Rubengera', 'Rugabano', 'Ruganda', 'Rwankuba', 'Twumba'],
                'Ngororero' => ['Bwira', 'Gatumba', 'Hindiro', 'Kabaya', 'Kageyo', 'Kavumu', 'Matyazo', 'Muhanda', 'Muhororo', 'Ndaro', 'Ngororero', 'Nyange', 'Sovu'],
                'Nyabihu' => ['Bigogwe', 'Jenda', 'Jomba', 'Kabatwa', 'Karago', 'Kintobo', 'Mukamira', 'Mulinga', 'Rambura', 'Rugera', 'Rurembo', 'Shyira'],
                'Nyamasheke' => ['Bushekeri', 'Bushenge', 'Cyato', 'Gihombo', 'Kagano', 'Kanjongo', 'Karambi', 'Karengera', 'Kirimbi', 'Macuba', 'Mahembe', 'Nyabitekeri', 'Rangiro', 'Ruharambuga', 'Shangi'],
                'Rubavu' => ['Bugeshi', 'Busasamana', 'Cyanzarwe', 'Gisenyi', 'Kanama', 'Kanzenze', 'Mudende', 'Nyakiliba', 'Nyamyumba', 'Nyundo', 'Rubavu', 'Rugerero'],
                'Rusizi' => ['Bugarama', 'Butare', 'Bweyeye', 'Gashonga', 'Giheke', 'Gihundwe', 'Gikundamvura', 'Gitambi', 'Kamembe', 'Muganza', 'Mururu', 'Nkanka', 'Nkombo', 'Nkungu', 'Nyakabuye', 'Nyakarenzo', 'Nzahaha', 'Rwimbogo'],
                'Rutsiro' => ['Boneza', 'Gihango', 'Kigeyo', 'Kivumu', 'Manihira', 'Mukura', 'Murunda', 'Musasa', 'Mushonyi', 'Mushubati', 'Nyabirasi', 'Ruhango', 'Rusebeya'],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function provinces(): array
    {
        return array_keys(self::tree());
    }

    /**
     * @return list<string>
     */
    public static function districts(?string $province = null): array
    {
        $tree = self::tree();

        if ($province === null || $province === '') {
            $all = [];
            foreach ($tree as $districts) {
                $all = array_merge($all, array_keys($districts));
            }

            return array_values(array_unique($all));
        }

        return array_keys($tree[$province] ?? []);
    }

    /**
     * @return list<string>
     */
    public static function sectors(?string $province = null, ?string $district = null): array
    {
        $tree = self::tree();

        if ($province && $district) {
            return $tree[$province][$district] ?? [];
        }

        if ($province) {
            $all = [];
            foreach ($tree[$province] ?? [] as $sectors) {
                $all = array_merge($all, $sectors);
            }

            return array_values(array_unique($all));
        }

        return [];
    }

    public static function isValidProvince(string $province): bool
    {
        return array_key_exists($province, self::tree());
    }

    public static function isValidDistrict(string $province, string $district): bool
    {
        return array_key_exists($district, self::tree()[$province] ?? []);
    }

    public static function isValidSector(string $province, string $district, string $sector): bool
    {
        return in_array($sector, self::tree()[$province][$district] ?? [], true);
    }

    /**
     * Validation rules for registration / profile / admin user forms.
     *
     * @return array<string, mixed>
     */
    public static function validationRules(bool $rwandaHierarchyRequired = true): array
    {
        $countries = self::countries();

        $rules = [
            'country' => ['required', 'string', 'max:100', 'in:'.implode(',', $countries)],
            'province' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'sector' => ['nullable', 'string', 'max:100'],
        ];

        if ($rwandaHierarchyRequired) {
            $rules['province'] = ['required_if:country,Rwanda', 'nullable', 'string', 'max:100'];
            $rules['district'] = ['required_if:country,Rwanda', 'nullable', 'string', 'max:100'];
            $rules['sector'] = ['required_if:country,Rwanda', 'nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    /**
     * @param  array{country?:string,province?:string,district?:string,sector?:string}  $data
     * @return array<string, list<string>>
     */
    public static function validateHierarchy(array $data): array
    {
        $errors = [];
        $country = $data['country'] ?? null;

        if ($country !== self::DEFAULT_COUNTRY) {
            return $errors;
        }

        $province = trim((string) ($data['province'] ?? ''));
        $district = trim((string) ($data['district'] ?? ''));
        $sector = trim((string) ($data['sector'] ?? ''));

        if ($province === '' || ! self::isValidProvince($province)) {
            $errors['province'] = ['Please select a valid province.'];
        }

        if ($district === '' || ! self::isValidDistrict($province, $district)) {
            $errors['district'] = ['Please select a valid district for the chosen province.'];
        }

        if ($sector === '' || ! self::isValidSector($province, $district, $sector)) {
            $errors['sector'] = ['Please select a valid sector for the chosen district.'];
        }

        return $errors;
    }

    public static function format(?string $country, ?string $province = null, ?string $district = null, ?string $sector = null): string
    {
        return collect([$sector, $district, $province, $country])
            ->filter(fn ($part) => filled($part))
            ->implode(', ');
    }
}
