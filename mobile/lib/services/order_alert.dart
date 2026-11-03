import 'dart:async';

import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/foundation.dart';

/// Loops an alert sound for up to [maxDuration], stoppable by staff.
class OrderAlertService extends ChangeNotifier {
  OrderAlertService() {
    _player.setReleaseMode(ReleaseMode.loop);
    _player.setVolume(1.0);
  }

  final AudioPlayer _player = AudioPlayer();
  Timer? _autoStop;
  bool _ringing = false;
  String? _message;

  bool get isRinging => _ringing;
  String? get message => _message;

  Future<void> ringNewKitchenOrder() => ring(
        asset: 'sounds/kitchen-new.wav',
        message: 'New kitchen order — tap Stop when you see it',
      );

  Future<void> ringKitchenReady() => ring(
        asset: 'sounds/cashier-ready.wav',
        message: 'Kitchen order ready — tap Stop when you see it',
      );

  Future<void> ring({
    required String asset,
    required String message,
    Duration maxDuration = const Duration(seconds: 10),
  }) async {
    await stop(notify: false);
    _ringing = true;
    _message = message;
    notifyListeners();

    try {
      await _player.play(AssetSource(asset));
    } catch (e) {
      debugPrint('Order alert play failed: $e');
    }

    _autoStop = Timer(maxDuration, () {
      stop();
    });
  }

  Future<void> stop({bool notify = true}) async {
    _autoStop?.cancel();
    _autoStop = null;
    try {
      await _player.stop();
    } catch (_) {}
    final wasRinging = _ringing;
    _ringing = false;
    _message = null;
    if (notify && wasRinging) {
      notifyListeners();
    }
  }

  @override
  void dispose() {
    _autoStop?.cancel();
    _player.dispose();
    super.dispose();
  }
}
