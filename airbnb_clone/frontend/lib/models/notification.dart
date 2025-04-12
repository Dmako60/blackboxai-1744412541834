import 'user.dart';
import 'property.dart';
import 'reservation.dart';
import 'payment.dart';

enum NotificationType {
  reservation,
  payment,
  review,
  system,
  promotion,
  security;

  String get displayName => name[0].toUpperCase() + name.substring(1).toLowerCase();
  
  String get icon {
    switch (this) {
      case NotificationType.reservation:
        return '🏠';
      case NotificationType.payment:
        return '💰';
      case NotificationType.review:
        return '⭐';
      case NotificationType.system:
        return '🔔';
      case NotificationType.promotion:
        return '🎉';
      case NotificationType.security:
        return '🔒';
    }
  }
}

class Notification {
  final int id;
  final int userId;
  final String title;
  final String message;
  final NotificationType type;
  final Map<String, dynamic>? data;
  final bool isRead;
  final User? user;
  final Property? property;
  final Reservation? reservation;
  final Payment? payment;
  final DateTime createdAt;
  final DateTime updatedAt;

  Notification({
    required this.id,
    required this.userId,
    required this.title,
    required this.message,
    required this.type,
    this.data,
    required this.isRead,
    this.user,
    this.property,
    this.reservation,
    this.payment,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Notification.fromJson(Map<String, dynamic> json) {
    return Notification(
      id: json['id'],
      userId: json['user_id'],
      title: json['title'],
      message: json['message'],
      type: NotificationType.values.firstWhere(
        (e) => e.name == json['type'],
        orElse: () => NotificationType.system,
      ),
      data: json['data'] != null 
          ? Map<String, dynamic>.from(json['data'])
          : null,
      isRead: json['is_read'] == 1 || json['is_read'] == true,
      user: json['user'] != null ? User.fromJson(json['user']) : null,
      property: json['property'] != null ? Property.fromJson(json['property']) : null,
      reservation: json['reservation'] != null ? Reservation.fromJson(json['reservation']) : null,
      payment: json['payment'] != null ? Payment.fromJson(json['payment']) : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'title': title,
      'message': message,
      'type': type.name,
      'data': data,
      'is_read': isRead,
      'user': user?.toJson(),
      'property': property?.toJson(),
      'reservation': reservation?.toJson(),
      'payment': payment?.toJson(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Notification copyWith({
    int? id,
    int? userId,
    String? title,
    String? message,
    NotificationType? type,
    Map<String, dynamic>? data,
    bool? isRead,
    User? user,
    Property? property,
    Reservation? reservation,
    Payment? payment,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Notification(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      title: title ?? this.title,
      message: message ?? this.message,
      type: type ?? this.type,
      data: data ?? this.data,
      isRead: isRead ?? this.isRead,
      user: user ?? this.user,
      property: property ?? this.property,
      reservation: reservation ?? this.reservation,
      payment: payment ?? this.payment,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  String get timeAgo {
    final now = DateTime.now();
    final difference = now.difference(createdAt);

    if (difference.inDays > 7) {
      return createdAt.toString().substring(0, 10);
    } else if (difference.inDays > 0) {
      return '${difference.inDays}d ago';
    } else if (difference.inHours > 0) {
      return '${difference.inHours}h ago';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes}m ago';
    } else {
      return 'Just now';
    }
  }

  String get icon => type.icon;

  bool get requiresAction {
    switch (type) {
      case NotificationType.payment:
        return payment?.status.toLowerCase() == 'pending';
      case NotificationType.reservation:
        return reservation?.status.toLowerCase() == 'pending';
      case NotificationType.security:
        return !isRead;
      default:
        return false;
    }
  }

  bool get isUrgent {
    if (type == NotificationType.security) return true;
    if (type == NotificationType.payment && payment?.status.toLowerCase() == 'pending') return true;
    return false;
  }

  String? get actionUrl {
    switch (type) {
      case NotificationType.reservation:
        return reservation != null ? '/reservations/${reservation!.id}' : null;
      case NotificationType.payment:
        return payment != null ? '/payments/${payment!.id}' : null;
      case NotificationType.property:
        return property != null ? '/properties/${property!.id}' : null;
      default:
        return data?['action_url'];
    }
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
  
    return other is Notification &&
      other.id == id &&
      other.userId == userId &&
      other.title == title &&
      other.message == message &&
      other.type == type &&
      other.isRead == isRead;
  }

  @override
  int get hashCode {
    return id.hashCode ^
      userId.hashCode ^
      title.hashCode ^
      message.hashCode ^
      type.hashCode ^
      isRead.hashCode;
  }
}
