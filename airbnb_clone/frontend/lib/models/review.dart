import 'user.dart';
import 'property.dart';
import 'reservation.dart';

class Review {
  final int id;
  final int userId;
  final int propertyId;
  final int? reservationId;
  final double rating;
  final String comment;
  final List<String>? photos;
  final User? user;
  final Property? property;
  final Reservation? reservation;
  final DateTime createdAt;
  final DateTime updatedAt;

  Review({
    required this.id,
    required this.userId,
    required this.propertyId,
    this.reservationId,
    required this.rating,
    required this.comment,
    this.photos,
    this.user,
    this.property,
    this.reservation,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    return Review(
      id: json['id'],
      userId: json['user_id'],
      propertyId: json['property_id'],
      reservationId: json['reservation_id'],
      rating: double.parse(json['rating'].toString()),
      comment: json['comment'],
      photos: json['photos'] != null 
          ? List<String>.from(json['photos'])
          : null,
      user: json['user'] != null 
          ? User.fromJson(json['user'])
          : null,
      property: json['property'] != null 
          ? Property.fromJson(json['property'])
          : null,
      reservation: json['reservation'] != null 
          ? Reservation.fromJson(json['reservation'])
          : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'property_id': propertyId,
      'reservation_id': reservationId,
      'rating': rating,
      'comment': comment,
      'photos': photos,
      'user': user?.toJson(),
      'property': property?.toJson(),
      'reservation': reservation?.toJson(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Review copyWith({
    int? id,
    int? userId,
    int? propertyId,
    int? reservationId,
    double? rating,
    String? comment,
    List<String>? photos,
    User? user,
    Property? property,
    Reservation? reservation,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Review(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      propertyId: propertyId ?? this.propertyId,
      reservationId: reservationId ?? this.reservationId,
      rating: rating ?? this.rating,
      comment: comment ?? this.comment,
      photos: photos ?? this.photos,
      user: user ?? this.user,
      property: property ?? this.property,
      reservation: reservation ?? this.reservation,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  String get formattedDate {
    return '${createdAt.day}/${createdAt.month}/${createdAt.year}';
  }

  String get formattedRating {
    return rating.toStringAsFixed(1);
  }

  String get ratingStars {
    return '★' * rating.round() + '☆' * (5 - rating.round());
  }

  bool get hasPhotos => photos != null && photos!.isNotEmpty;

  bool get isVerifiedStay => reservation != null;

  bool get canBeEdited {
    final now = DateTime.now();
    return now.difference(createdAt).inDays <= 3; // 3-day edit window
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
  
    return other is Review &&
      other.id == id &&
      other.userId == userId &&
      other.propertyId == propertyId &&
      other.reservationId == reservationId &&
      other.rating == rating &&
      other.comment == comment;
  }

  @override
  int get hashCode {
    return id.hashCode ^
      userId.hashCode ^
      propertyId.hashCode ^
      reservationId.hashCode ^
      rating.hashCode ^
      comment.hashCode;
  }
}
