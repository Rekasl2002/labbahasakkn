<?php

namespace App\Controllers;

use App\Models\SessionModel;
use App\Models\ParticipantModel;
use App\Models\SessionStateModel;
use App\Models\MaterialModel;
use App\Models\MaterialFileModel;
use App\Models\EventModel;

class StudentController extends BaseController
{
    private function clearStudentSessionData(): void
    {
        session()->remove([
            'participant_id',
            'session_id',
            'student_name',
            'class_name',
            'device_label',
            'student_waiting',
            'waiting_student_profile',
        ]);
    }

    /**
     * @return array{
     *   mode: 'active'|'waiting'|'expired'|'none',
     *   session_id?: int,
     *   participant_id?: int,
     *   participant?: array,
     *   profile?: array{student_name:string,class_name:string,device_label:string}
     * }
     */
    private function resolveSettingsContext(): array
    {
        $sessionId = (int) session()->get('session_id');
        $participantId = (int) session()->get('participant_id');

        if ($sessionId > 0 && $participantId > 0) {
            $active = $this->getActiveSessionRaw();
            $isActiveMatch = $active && (int) ($active['id'] ?? 0) === $sessionId;
            if (!$isActiveMatch) {
                return ['mode' => 'expired'];
            }

            $participant = (new ParticipantModel())
                ->where('id', $participantId)
                ->where('session_id', $sessionId)
                ->first();
            if (!$participant) {
                return ['mode' => 'expired'];
            }

            return [
                'mode' => 'active',
                'session_id' => $sessionId,
                'participant_id' => $participantId,
                'participant' => $participant,
                'profile' => [
                    'student_name' => (string) ($participant['student_name'] ?? ''),
                    'class_name' => (string) ($participant['class_name'] ?? ''),
                    'device_label' => (string) ($participant['device_label'] ?? ''),
                ],
            ];
        }

        $waitingProfile = session()->get('waiting_student_profile');
        $hasWaitingProfile = is_array($waitingProfile);
        if (!$hasWaitingProfile) {
            $waitingProfile = [];
        }

        $studentName = trim((string) ($waitingProfile['student_name'] ?? session('student_name') ?? ''));
        $className = trim((string) ($waitingProfile['class_name'] ?? session('class_name') ?? ''));
        $deviceLabel = trim((string) ($waitingProfile['device_label'] ?? session('device_label') ?? ''));
        $isWaiting = (bool) session()->get('student_waiting') || $hasWaitingProfile;

        if ($isWaiting && $studentName !== '' && $className !== '') {
            return [
                'mode' => 'waiting',
                'profile' => [
                    'student_name' => $studentName,
                    'class_name' => $className,
                    'device_label' => $deviceLabel,
                ],
            ];
        }

        return ['mode' => 'none'];
    }

    private function settingsTarget(bool $embed): string
    {
        return '/student/settings?tab=general' . ($embed ? '&embed=1' : '');
    }

    private function parseTextItems(array $material): array
    {
        $type = (string) ($material['type'] ?? '');
        if ($type !== 'folder') return [];

        $raw = (string) ($material['text_content'] ?? '');
        if ($raw === '') return [];

        $lines = preg_split("/\r\n|\n|\r/", $raw);
        $out = [];
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $line = trim((string) $line);
                if ($line !== '') $out[] = $line;
            }
        }
        return $out;
    }

    private function buildCurrentMaterial(?array $material, ?array $state): ?array
    {
        if (!$material) return null;

        $files = (new MaterialFileModel())
            ->orderedForMaterial((int) $material['id'])
            ->findAll();

        foreach ($files as &$f) {
            if (isset($f['url_path'])) $f['url_path'] = (string) $f['url_path'];
            if (isset($f['preview_url_path'])) $f['preview_url_path'] = (string) $f['preview_url_path'];
            if (isset($f['cover_url_path'])) $f['cover_url_path'] = (string) $f['cover_url_path'];
        }
        unset($f);

        $textItems = $this->parseTextItems($material);

        $selected = null;
        $fileId = isset($state['current_material_file_id']) ? (int) $state['current_material_file_id'] : 0;
        $textIndexRaw = $state['current_material_text_index'] ?? null;

        if ($fileId > 0) {
            foreach ($files as $f) {
                if ((int) $f['id'] === $fileId) {
                    $selected = ['type' => 'file', 'file' => $f];
                    break;
                }
            }
        }

        if (!$selected && $textIndexRaw !== null && $textIndexRaw !== '') {
            $idx = (int) $textIndexRaw;
            if (isset($textItems[$idx])) {
                $selected = ['type' => 'text', 'index' => $idx, 'text' => $textItems[$idx]];
            }
        }

        if (!$selected) {
            $type = (string) ($material['type'] ?? '');
            if ($type === 'text' && !empty($material['text_content'])) {
                $selected = [
                    'type' => 'text',
                    'index' => null,
                    'text' => (string) $material['text_content'],
                    'mode' => 'full',
                ];
            } elseif ($type === 'file' && !empty($files)) {
                $selected = ['type' => 'file', 'file' => $files[0], 'mode' => 'default'];
            } elseif ($type === 'folder') {
                if (!empty($textItems)) {
                    $selected = ['type' => 'text', 'index' => 0, 'text' => $textItems[0], 'mode' => 'default'];
                } elseif (!empty($files)) {
                    $selected = ['type' => 'file', 'file' => $files[0], 'mode' => 'default'];
                }
            }
        }

        return [
            'material' => $material,
            'files' => $files,
            'text_items' => $textItems,
            'selected' => $selected,
        ];
    }

    public function dashboard()
    {
        $sessionId = (int) session()->get('session_id');
        $participantId = (int) session()->get('participant_id');

        if ($sessionId <= 0 || $participantId <= 0) {
            return redirect()->to('/login');
        }

        $active = $this->getActiveSession();
        if (!$active || (int) ($active['id'] ?? 0) !== $sessionId) {
            helper('remember');
            $this->clearStudentSessionData();
            $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
            return redirect()->to('/login')->with('ok', 'Sesi sudah berakhir. Silakan tunggu sesi berikutnya.');
        }

        $session = (new SessionModel())->find($sessionId);
        $me = (new ParticipantModel())
            ->where('id', $participantId)
            ->where('session_id', $sessionId)
            ->first();
        if (!$me) {
            helper('remember');
            $this->clearStudentSessionData();
            $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
            return redirect()->to('/login')->with('error', 'Data siswa pada sesi ini tidak ditemukan.');
        }

        session()->set([
            'student_name' => (string) ($me['student_name'] ?? ''),
            'class_name' => (string) ($me['class_name'] ?? ''),
            'device_label' => (string) ($me['device_label'] ?? ''),
        ]);
        session()->remove(['student_waiting', 'waiting_student_profile']);

        $state = (new SessionStateModel())->where('session_id', $sessionId)->first();
        $currentMaterial = null;

        if ($state && !empty($state['current_material_id'])) {
            $material = (new MaterialModel())->find((int)$state['current_material_id']);
            $currentMaterial = $this->buildCurrentMaterial($material, $state);
        }

        return view('student/dashboard', [
            'session' => $session,
            'me' => $me,
            'state' => $state,
            'currentMaterial' => $currentMaterial,
        ]);
    }

    public function settings()
    {
        $tab = (string) $this->request->getGet('tab');
        $tab = $tab !== '' ? $tab : 'general';
        $allowedTabs = ['general'];
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'general';
        }
        $embed = (string) $this->request->getGet('embed') === '1';
        $context = $this->resolveSettingsContext();
        $mode = (string) ($context['mode'] ?? 'none');

        if ($mode === 'expired') {
            helper('remember');
            $this->clearStudentSessionData();
            $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
            return redirect()->to('/login')->with('ok', 'Sesi sudah berakhir. Silakan tunggu sesi berikutnya.');
        }
        if ($mode === 'none') {
            return redirect()->to('/login')->with('error', 'Data siswa belum tersedia. Silakan isi dari halaman login.');
        }

        $profile = $context['profile'] ?? [
            'student_name' => '',
            'class_name' => '',
            'device_label' => '',
        ];
        $returnUrl = $mode === 'active' ? '/student' : '/login';
        $modeLabel = $mode === 'active' ? 'Sedang sesi' : 'Menunggu sesi';

        return view($embed ? 'student/settings/embed' : 'student/settings/index', [
            'tab' => $tab,
            'embed' => $embed,
            'profile' => $profile,
            'mode' => $mode,
            'mode_label' => $modeLabel,
            'return_url' => $returnUrl,
        ]);
    }

    public function saveSettings()
    {
        $embed = (string) $this->request->getPost('embed') === '1';
        $target = $this->settingsTarget($embed);

        $studentName = trim($this->postString('student_name', 60));
        $className = trim($this->postString('class_name', 60));
        $deviceLabel = trim($this->postString('device_label', 60));

        if ($studentName === '' || $className === '') {
            return redirect()->to($target)->with('error', 'Nama lengkap dan kelas wajib diisi.');
        }

        $context = $this->resolveSettingsContext();
        $mode = (string) ($context['mode'] ?? 'none');

        if ($mode === 'expired') {
            helper('remember');
            $this->clearStudentSessionData();
            $this->response->deleteCookie(LAB_COOKIE_PARTICIPANT);
            return redirect()->to('/login')->with('ok', 'Sesi sudah berakhir. Silakan tunggu sesi berikutnya.');
        }

        if ($mode === 'none') {
            return redirect()->to('/login')->with('error', 'Data siswa belum tersedia. Silakan isi dari halaman login.');
        }

        if ($mode === 'waiting') {
            session()->set([
                'student_waiting' => 1,
                'waiting_student_profile' => [
                    'student_name' => $studentName,
                    'class_name' => $className,
                    'device_label' => $deviceLabel,
                ],
                'student_name' => $studentName,
                'class_name' => $className,
                'device_label' => $deviceLabel,
            ]);

            return redirect()->to($target)->with('ok', 'Profil siswa berhasil diperbarui.');
        }

        $participantId = (int) ($context['participant_id'] ?? 0);
        $sessionId = (int) ($context['session_id'] ?? 0);
        $participant = $context['participant'] ?? null;

        if ($participantId <= 0 || $sessionId <= 0 || !is_array($participant)) {
            return redirect()->to('/login')->with('error', 'Data sesi siswa tidak valid.');
        }

        (new ParticipantModel())->update($participantId, [
            'student_name' => $studentName,
            'class_name' => $className,
            'device_label' => $deviceLabel !== '' ? $deviceLabel : null,
            'last_seen_at' => date('Y-m-d H:i:s'),
        ]);

        session()->set([
            'student_name' => $studentName,
            'class_name' => $className,
            'device_label' => $deviceLabel,
        ]);
        session()->remove(['student_waiting', 'waiting_student_profile']);

        (new EventModel())->addForAll($sessionId, 'participant_updated', [
            'participant_id' => $participantId,
            'student_name' => $studentName,
            'class_name' => $className,
            'device_label' => $deviceLabel,
            'mic_on' => !empty($participant['mic_on']) ? 1 : 0,
            'speaker_on' => !empty($participant['speaker_on']) ? 1 : 0,
        ]);

        return redirect()->to($target)->with('ok', 'Profil siswa berhasil diperbarui.');
    }
}
