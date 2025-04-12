import 'package:flutter/foundation.dart';
import 'agent.dart';

class Property {
  final int id;
  final int agentId;
  final String title;
  final String description;
  final String location;
  final double pricePerNight;
  final int bedrooms;
  final int bathrooms;
  final int maxGuests;
  final List<String> amenities;
  final List<String> images;
  final String status;
  final double rating;
  final int reviewCount;
  final Agent? agent;
  final DateTime createdAt;
  final DateTime updatedAt;

  Property({
    required this.id,
    required this.agentId,
    required this.title,
    required this.description,
    required this.location,
    required this.pricePerNight,
    required this.bedrooms,
    required this.bathrooms,
    required this.maxGuests,
    required this.amenities,
    required this.images,
    required this.status,
    required this.rating,
    required this.reviewCount,
    this.agent,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Property.fromJson(Map<String, dynamic> json) {
    return Property(
      id: json['id'],
      agentId: json['agent_id'],
      title: json['title'],
      description: json['description'],
      location: json['location'],
      pricePerNight: double.parse(json['price_per_night'].toString()),
      bedrooms: json['bedrooms'],
      bathrooms: json['bathrooms'],
      maxGuests: json['max_guests'],
      amenities: List<String>.from(json['amenities'] ?? []),
      images: List<String>.from(json['images'] ?? []),
      status: json['status'],
      rating: double.parse(json['rating']?.toString() ?? '0.0'),
      reviewCount: json['review_count'] ?? 0,
      agent: json['agent'] != null ? Agent.fromJson(json['agent']) : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'agent_id': agentId,
      'title': title,
      'description': description,
      'location': location,
      'price_per_night': pricePerNight,
      'bedrooms': bedrooms,
      'bathrooms': bathrooms,
      'max_guests': maxGuests,
      'amenities': amenities,
      'images': images,
      'status': status,
      'rating': rating,
      'review_count': reviewCount,
      'agent': agent?.toJson(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Property copyWith({
    int? id,
    int? agentId,
    String? title,
    String? description,
    String? location,
    double? pricePerNight,
    int? bedrooms,
    int? bathrooms,
    int? maxGuests,
    List<String>? amenities,
    List<String>? images,
    String? status,
    double? rating,
    int? reviewCount,
    Agent? agent,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Property(
      id: id ?? this.id,
      agentId: agentId ?? this.agentId,
      title: title ?? this.title,
      description: description ?? this.description,
      location: location ?? this.location,
      pricePerNight: pricePerNight ?? this.pricePerNight,
      bedrooms: bedrooms ?? this.bedrooms,
      bathrooms: bathrooms ?? this.bathrooms,
      maxGuests: maxGuests ?? this.maxGuests,
      amenities: amenities ?? this.amenities,
      images: images ?? this.images,
      status: status ?? this.status,
      rating: rating ?? this.rating,
      reviewCount: reviewCount ?? this.reviewCount,
      agent: agent ?? this.agent,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
  
    return other is Property &&
      other.id == id &&
      other.agentId == agentId &&
      other.title == title &&
      other.description == description &&
      other.location == location &&
      other.pricePerNight == pricePerNight &&
      other.bedrooms == bedrooms &&
      other.bathrooms == bathrooms &&
      other.maxGuests == maxGuests &&
      listEquals(other.amenities, amenities) &&
      listEquals(other.images, images) &&
      other.status == status &&
      other.rating == rating &&
      other.reviewCount == reviewCount;
  }

  @override
  int get hashCode {
    return id.hashCode ^
      agentId.hashCode ^
      title.hashCode ^
      description.hashCode ^
      location.hashCode ^
      pricePerNight.hashCode ^
      bedrooms.hashCode ^
      bathrooms.hashCode ^
      maxGuests.hashCode ^
      amenities.hashCode ^
      images.hashCode ^
      status.hashCode ^
      rating.hashCode ^
      reviewCount.hashCode;
  }
}
