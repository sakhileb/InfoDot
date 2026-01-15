<?php

namespace App\Notifications;

use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TeamInvitation $invitation,
        public Team $team
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('team-invitations.accept', ['invitation' => $this->invitation]);

        return (new MailMessage)
            ->subject('Team Invitation - InfoDot')
            ->greeting('Hello!')
            ->line('You have been invited to join the team "' . $this->team->name . '".')
            ->line('Team owner: ' . $this->team->owner->name)
            ->line('Role: ' . ucfirst($this->invitation->role))
            ->action('Accept Invitation', $url)
            ->line('If you did not expect to receive an invitation to this team, you may disregard this email.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'invitation_id' => $this->invitation->id,
            'team_id' => $this->team->id,
            'team_name' => $this->team->name,
            'team_owner_name' => $this->team->owner->name,
            'team_owner_id' => $this->team->user_id,
            'role' => $this->invitation->role,
        ];
    }
}
