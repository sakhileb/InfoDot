<?php

namespace App\Notifications\Campaigns;

use App\User;
use App\Campaign;
use Illuminate\Bus\Queueable;
use App\Http\Resources\UserResource;
use App\Http\Resources\CampaignResource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DatabaseNotificationChannel;
use Illuminate\Notifications\Notification;

class CampaignLiked extends Notification
{
    use Queueable;

    protected $user;

    protected $campaign;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user, Campaign $campaign)
    {
        $this->user = $user;
        $this->campaign = $campaign;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            DatabaseNotificationChannel::class
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user' => new UserResource($this->user),
            'campaign' => new CampaignResource($this->campaign),
        ];
    }
}
