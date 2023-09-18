<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Services\TelegramBot;
use App\Models\User;
use App\Models\Role;
use App\Models\Booking;
use App\Models\Service;
class BotController extends Controller
{
    private $message;
    private $chat_id;
    private $is_local;

    public function webhook()
    {
        if (request()->get('secret') == 'roomhair') {
            $this->is_local = false;
        } else {
            $this->is_local = true;
        }

        $request = json_decode(file_get_contents('php://input'));

        $bot = new TelegramBot($request);

        $bot->is_local = $this->is_local;
        if ($bot->message == "/start") {
			$bot->saveCommand("start");
			$bot->sendMessage("Пожалуйста, представьтесь:");
		} else if($bot->message == "/contacts") {
            $bot->sendMessage("Телефон: +998909601313\nСайт: roomhair.uz\nInstagram: https://instagram.com/roomhair.uz\n\nДля брони, нажмите /start:");
        } else if($bot->message == "/my") {
            $master = User::where('telegram_chat_id', $bot->chat_id)->get()->first();
            if($master && $master->count() > 0) {
                $bookings = Booking::where('start_at', '>', date('Y-m-d H:i:s'))->where('master_id', $master->id)->orderBy('start_at')->get();
                $text = "";
                foreach ($bookings as $booking) {
                    $text .= sprintf("%s на %s в %s (%s)\n",
                        $booking->name,
                        $booking->service->name,
                        date("d.m.Y H:i", strtotime($booking->start_at)),
                        Str::startsWith($booking->phone, '+') ? $booking->phone : "+" . $booking->phone,
                    );
                }
                if ($text != "") {
                    $bot->sendMessage($text);
                }
            }
        } else {
            if(!isset($bot->callback_query)){
                $command = $bot->getLastCommand();
                // последняя команда была ввод города, теперь получаем адрес
                if ($command == "start") {
                    $bot->client->name = $bot->message;
                    $bot->client->save();
                    $bot->saveCommand("name");
                    $kbd = json_encode([
                        'keyboard' => [
                            [
                                ['text' => __('📞 Поделиться контактом'), 'request_contact' => true]
                            ]
                        ],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                    ]);
                    $bot->sendMessage("Поделитесь номером телефона, нажав на кнопку 'Поделиться контактом':", $kbd);
                }
                if ($command == "name" && isset($bot->update->message->contact)) {

                    $bot->client->phone = $bot->update->message->contact->phone_number;

                    $bot->client->save();

                    $arr = [];
                    $services = Service::has('masters')->get();
                    foreach ($services as $service) {
                        $arr[] = [[
                            'text' => $service->name,
                            'callback_data' => 'select_service|' . $service->id,
                        ]];
                    }
                    $kbd = json_encode([
                        'inline_keyboard' => $arr,
                    ]);
                    $bot->sendMessage("Выберите услугу:", $kbd);
                }
            }
		}
		if(isset($bot->callback_query)) {
			$data = explode('|',$bot->callback_query->data);
            if($data[0] == 'list_services')
            {
                $arr = [];
                $services = Service::has('masters')->get();
                foreach ($services as $service) {
                    $arr[] = [[
                        'text' => $service->name,
                        'callback_data' => 'select_service|' . $service->id,
                    ]];
                }
                $kbd = json_encode([
                    'inline_keyboard' => $arr
                ]);
                $bot->sendMessage("Выберите услугу:", $kbd);
            }
            if ($data[0] == 'select_service')
			{
				$service_id = $data[1];
                $service = Service::find($service_id);
                $booking = Booking::updateOrCreate(
                    ['telegram_chat_id' => $bot->chat_id, 'status' => 'pending'],
                    ['service_id' => $service_id, 'name' => $bot->client->name, 'phone' => $bot->client->phone, 'client_id' => $bot->client->id]
                );

                $masters = Role::where('name', 'master')->first()->users;

                $arr = [];
                foreach ($masters as $master) {
                    if ($master->hasService($service_id)) {
                        $arr[] = [[
                            'text' => $master->name,
                            'callback_data' => 'select_master|' . $master->id,
                        ]];
                    }
                }
                $arr[] = [[
                    'text' => '◀️ К списку услуг',
                    'callback_data' => 'list_services',
                ]];
                $kbd = json_encode([
                    'inline_keyboard' => $arr
                ]);
                $text = str_replace("  ", " ", strip_tags($service->description));
                $bot->sendMessage($text . "\n\nВыберите мастера:", $kbd);
                $bot->answerCallbackQuery();
			}
			if ($data[0] == 'select_master')
			{
				$master_id = $data[1];
                $booking = Booking::updateOrCreate(
                    ['telegram_chat_id' => $bot->chat_id, 'status' => 'pending'],
                    ['master_id' => $master_id]
                );
                $service = Service::find($booking->service_id);

                $kbd = TelegramBot::createMonthsKeyboard();
				$bot->sendMessage("Выберите месяц для записи:", $kbd);
                $bot->answerCallbackQuery();
			}

            if ($data[0] == 'booking_month')
			{
                $months = ['', 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
                $booking_month = $data[1];
                $booking = Booking::updateOrCreate(
                    ['telegram_chat_id' => $bot->chat_id, 'status' => 'pending'],
                    ['start_at' => date('Y-' . $booking_month . '-01')]
                );
                $kbd = TelegramBot::createDaysKeyboard($booking->start_at);
                $bot->sendMessage("Выберите день (" . ($months[$booking_month]) . ", " . date("Y"). ")", $kbd);
                $bot->answerCallbackQuery();
            }
            if ($data[0] == 'booking_day')
			{
                $booking_day = $data[1];
                $booking = Booking::where('telegram_chat_id', $bot->chat_id)
                    ->where('status', 'pending')->get()->first();
                $start_at = date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m', strtotime($booking->start_at)), $booking_day, date('Y')));

                if ($start_at < date('Y-m-d 00:00:00')) {
                    $kbd = TelegramBot::createMonthsKeyboard();
                    $bot->sendMessage("Вы указали некорректную дату. Выберите месяц:", $kbd);
                    $bot->answerCallbackQuery();

                } else {
                    $date = $request->date;
                    $booking = Booking::updateOrCreate(
                        ['telegram_chat_id' => $bot->chat_id, 'status' => 'pending'],
                        ['start_at' => $start_at]
                    );

                    $start_at = date('Y-m-d', strtotime($start_at)) . ' 09:00:00';
                    $end_at = date('Y-m-d', strtotime($start_at)) . ' 18:00:00';

                    $arr = [];
                    $free_time_slots = Booking::free_time_slots($start_at, $end_at, $booking->master);

                    foreach ($free_time_slots as $slot) {
                        $arr[] = [[
                            'text' => $slot['from'] . ' - ' . $slot['to'],
                            'callback_data' => 'booking_time|' . $slot['from'] . '|' . $slot['to'],
                        ]];
                    }
                    $kbd = json_encode([
                        'inline_keyboard' => $arr
                    ]);
                    $bot->sendMessage("Выберите время", $kbd);
                    $bot->answerCallbackQuery();
                }

            }
            if ($data[0] == 'booking_time')
			{
                $start_time = $data[1];
                $end_time = $data[2];
                $booking = Booking::where('telegram_chat_id', $bot->chat_id)
                    ->where('status', 'pending')->get()->first();
                $start_at = date('Y-m-d', strtotime($booking->start_at)) . ' ' . $start_time;
                $end_at   = date('Y-m-d', strtotime($booking->start_at)) . ' ' . $end_time;

                $booking->start_at = $start_at;
                $booking->end_at = $end_at;
                $booking->client_id = $bot->client->id;
                $booking->name = $bot->client->name;
                $booking->phone = $bot->client->phone;
                $booking->email = $bot->client->email;
                $booking->telegram_chat_id = $bot->chat_id;
                $booking->save();

                $arr = [
                    [
                        ['text' => '✅ Подтвердить (1 час)', 'callback_data' => 'confirm_booking|' . $booking->id . '|1']
                    ],
                    [
                        ['text' => '✅ Подтвердить (2 часа)', 'callback_data' => 'confirm_booking|' . $booking->id . '|2']
                    ],
                    [
                        ['text' => '✅ Подтвердить (3 часа)', 'callback_data' => 'confirm_booking|' . $booking->id . '|3']
                    ],
                    [
                        ['text' => '✅ Подтвердить (4 часа)', 'callback_data' => 'confirm_booking|' . $booking->id . '|4']
                    ],
                    [
                        ['text' => '❌ Удалить', 'callback_data' => 'delete_booking|' . $booking->id]
                    ]
                ];

                $kbd = json_encode([
                    'inline_keyboard' => $arr
                ]);

                $bot_admins = explode(',', config('app.bot_admins'));

                $text = sprintf("Новая бронь:\nИмя: %s\nТелефон: %s\n📅 Дата: %s\n🕔 Время: %s\n👩‍🦰 Мастер: %s\n🗒 Услуга: %s\nПодтвердите, указав, сколько времени займёт процедура:",
                    $booking->name,
                    $booking->phone,
                    date('d.m.Y', strtotime($booking->start_at)),
                    date('H:i', strtotime($booking->start_at)),
                    $booking->master->name,
                    $booking->service->name
                );

                foreach($bot_admins as $admin_id) {
                    $bot->sendMessage($text, $kbd, $admin_id, false);
                }

                $master_chat_id = $booking->master->telegram_chat_id;
                if(!is_null($master_chat_id)) {
                    $bot->sendMessage($text, $kbd, $master_chat_id, false);
                }

                $bot->sendLocation();
                $bot->sendMessage(sprintf("Готово! Ожидайте звонка администратора. Подробная информация:\n📅 Дата: %s\n🕔 Время: %s\n👩‍🦰 Мастер: %s\n🗒 Услуга: %s\nЖдём вас в салоне RoomHair!\n📞 Телефон для связи: +998909601313\n\nДля новой брони, нажмите /start",
                    date('d.m.Y', strtotime($booking->start_at)),
                    date('H:i', strtotime($booking->start_at)),
                    $booking->master->name,
                    $booking->service->name
                ));
            }
            if ($data[0] == 'delete_booking')
			{
                Booking::destroy($data[1]);
                $bot->sendMessage("Бронь удалена");
                $bot->answerCallbackQuery();
            }
            if ($data[0] == 'confirm_booking')
			{
                $booking = Booking::find($data[1]);
                if (isset($data[2]) && $data[2] > 1 && $data[2] <= 4) {
                    $start = Carbon::instance(new \DateTime($booking->start_at));
                    $end = $start->addHours(intval($data[2]));
                    $booking->end_at = $end->format("Y-m-d H:i:s");
                }
                $booking->status = "confirmed";
                $booking->save();
                $bot->sendMessage("Бронь подтверждена");
                $bot->answerCallbackQuery();
                //$bot->sendMessage("Ваша бронь подтверждена менеджером", false, $booking->client->telegram_chat_id);
            }
            if ($data[0] == 'empty')
			{
                $bot->answerCallbackQuery();
            }
		}
    }
}
